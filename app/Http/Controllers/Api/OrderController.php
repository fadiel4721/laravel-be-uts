<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Loyalty;
use App\Models\DiscountCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data pesanan dari database berdasarkan kasir_id
        $orders = Order::where('kasir_id', $request->kasir_id)->get();

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validasi request
            $request->validate([
                'transaction_time' => 'required|date',
                'kasir_id' => 'required|exists:users,id',
                'total_price' => 'required|numeric|min:0',
                'total_item' => 'required|numeric|min:1',
                'payment_method' => 'required|string|in:CASH,QR,TRANSFER',
                'discount_code' => 'nullable|string',
                'order_items' => 'required|array',
                'order_items.*.product_id' => 'required|exists:products,id',
                'order_items.*.quantity' => 'required|numeric|min:1',
                'order_items.*.total_price' => 'required|numeric|min:0',
            ]);
    
            // Get cashier and user data
            $user = User::findOrFail($request->kasir_id);
    
            // Get or create loyalty data
            $loyalty = Loyalty::firstOrNew(['user_id' => $user->id], [
                'total_spent' => 0,
                'level' => 0,
                'discount' => 0, // Default discount for level 0
            ]);
    
            $discountAmount = 0;
            $totalPrice = $request->total_price;
    
            // Check and process discount code if provided
            if ($request->filled('discount_code')) {
                $discountCode = $request->discount_code;
    
                // Check if discount code exists in loyalty table
                $discount = Loyalty::where('discount_code', $discountCode)->first();
    
                // If discount code exists in loyalty and has not been used
                if ($discount) {
                    // Check if discount code has already been used in previous orders
                    $discountUsed = Order::where('discount_code', $discountCode)->where('is_used', true)->exists();
    
                    if ($discountUsed) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Kode diskon telah digunakan sebelumnya.',
                        ], 400);
                    }
    
                    // Apply discount based on the loyalty data
                    $discountAmount = $totalPrice * ($discount->discount / 100);
                    $totalPrice -= $discountAmount; // Reduce total price by discount amount
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode diskon tidak valid.',
                    ], 400);
                }
            }
    
            // Create the order
            $order = Order::create([
                'transaction_time' => Carbon::parse($request->transaction_time),
                'kasir_id' => $request->kasir_id,
                'total_price' => $totalPrice,
                'total_item' => $request->total_item,
                'payment_method' => $request->payment_method,
                'discount_code' => $request->discount_code ?: null, // Store discount code used in order
                'is_used' => $discountAmount > 0, // Mark as used if discount applied
            ]);
    
            // Create order items
            foreach ($request->order_items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price'],
                ]);
            }
    
            // Update loyalty data (spending and level)
            $loyalty->total_spent += $totalPrice;
            $loyalty->level = Loyalty::calculateLevel($loyalty->total_spent)['level'];
            $loyalty->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Order successfully processed.',
                'order' => $order,
                'discount_applied' => $discountAmount > 0,
                'discount_amount' => $discountAmount,
            ]);
        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'error_message' => $e->getMessage(),
                'payload' => $request->all(),
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Error processing the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function validateDiscountCode(Request $request)
    {
        $userId = $request->user()->id;
        $discountCode = $request->input('discount_code');
    
        // Check if the discount code exists in the loyalty table for the user
        $loyalty = Loyalty::where('user_id', $userId)->where('discount_code', $discountCode)->first();
        if (!$loyalty) {
            return response()->json([
                'success' => false,
                'message' => 'Kode diskon tidak ditemukan atau tidak valid.',
            ], 400);
        }
    
        // Check if the discount code has already been used in a previous order
        $orderExists = Order::where('discount_code', $discountCode)->where('user_id', $userId)->exists();
        if ($orderExists) {
            return response()->json([
                'success' => false,
                'message' => 'Kode diskon telah digunakan sebelumnya.',
            ], 400);
        }
    
        // If not used, return discount percentage
        return response()->json([
            'success' => true,
            'discount_percentage' => $loyalty->discount,
        ]);
    }
    
    

    // Endpoint untuk melihat riwayat data order berdasarkan kasir_id
    public function getOrdersByKasirId(Request $request, $kasir_id)
    {
        try {
            $orders = Order::where('kasir_id', $kasir_id)
                ->with('orderItems.product') // Mengambil data order items beserta produk terkait
                ->orderBy('transaction_time', 'desc') // Mengurutkan berdasarkan waktu transaksi
                ->paginate(10); // Pagination

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada pesanan untuk kasir ini.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'orders' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    
  
    
}
