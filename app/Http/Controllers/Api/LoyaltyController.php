<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loyalty;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LoyaltyController extends Controller
{
    // Fungsi utama untuk menampilkan data loyalty pengguna
    public function index(Request $request)
    {
        // Ambil data pengguna berdasarkan token JWT
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }

        // Ambil data loyalty berdasarkan user_id
        $loyalty = Loyalty::where('user_id', $user->id)->first();

        if (!$loyalty) {
            // Jika tidak ada data loyalty, buatkan default data loyalty baru
            $loyalty = new Loyalty([
                'user_id' => $user->id,
                'total_spent' => 0,
                'level' => 0,
                'discount' => 0,
                'discount_code' => null
            ]);
            $loyalty->save();
        }

        // Periksa apakah level loyalitas perlu di-update dan kode diskon dibuat
        $this->checkLevelUpgradeAndAssignDiscount($loyalty);

        return response()->json([
            'success' => true,
            'data' => $loyalty
        ]);
    }

    // Fungsi untuk membuat kode diskon baru
    public function storeDiscountCode(Request $request)
    {
        // Validasi
        $request->validate([
            'discount_code' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        // Ambil loyalty berdasarkan user_id
        $loyalty = Loyalty::where('user_id', $request->user_id)->first();

        // Jika loyalty ditemukan, update discount_code
        if ($loyalty) {
            $loyalty->discount_code = $request->discount_code;
            $loyalty->save(); // Simpan perubahan
            return response()->json(['message' => 'Discount code saved successfully'], 200);
        }

        return response()->json(['message' => 'Loyalty not found'], 404);
    }


    public function show(Request $request)
    {
        // Ambil data pengguna berdasarkan token JWT
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }

        // Ambil data loyalty berdasarkan user_id
        $loyalty = Loyalty::where('user_id', $user->id)->first();

        if (!$loyalty) {
            // Jika tidak ada data loyalty, buatkan default data loyalty baru
            $loyalty = new Loyalty([
                'user_id' => $user->id,
                'total_spent' => 0,
                'level' => 0,
                'discount' => 0,
                'discount_code' => null
            ]);
            $loyalty->save();
        }

        // Periksa apakah level loyalitas perlu di-update
        $this->checkLevelUpgradeAndAssignDiscount($loyalty);

        // Validasi kode diskon jika ada di request
        $discountCode = $request->input('discount_code');
        if ($discountCode) {
            // Periksa apakah kode diskon valid
            if ($loyalty->discount_code === $discountCode) {
                // Cek apakah kode diskon sudah pernah digunakan
                $orderExists = Order::where('discount_code', $discountCode)
                    ->where('user_id', $user->id)
                    ->where('is_used', true) // Memastikan kode diskon sudah digunakan sebelumnya
                    ->exists();

                if ($orderExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode diskon telah digunakan sebelumnya.'
                    ], 400);
                }

                // Jika kode diskon valid dan belum digunakan, kembalikan diskon
                return response()->json([
                    'success' => true,
                    'message' => 'Kode diskon berhasil divalidasi!',
                    'discount_percentage' => $loyalty->discount
                ]);
            } else {
                // Jika kode diskon tidak valid
                return response()->json([
                    'success' => false,
                    'message' => 'Kode diskon tidak valid.'
                ], 400);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $loyalty
        ]);
    }


    public function upgradeLevelAndAssignDiscount(Request $request)
    {
        // Ambil data pengguna berdasarkan token JWT
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }

        // Ambil data loyalitas berdasarkan user_id
        $loyalty = Loyalty::where('user_id', $user->id)->first();

        if (!$loyalty) {
            return response()->json([
                'success' => false,
                'message' => 'Loyalty data not found.'
            ], 404);
        }

        // Periksa dan perbarui level serta kode diskon
        $this->checkLevelUpgradeAndAssignDiscount($loyalty);

        return response()->json([
            'success' => true,
            'message' => 'Level upgraded and discount code updated.',
            'level' => $loyalty->level,
            'discount' => $loyalty->discount,
            'discount_code' => $loyalty->discount_code,
        ]);
    }

    // Fungsi untuk mengubah level dan assign diskon
    private function checkLevelUpgradeAndAssignDiscount($loyalty)
    {
        Log::info("Checking loyalty level upgrade for user_id: {$loyalty->user_id}");
    
        // Simpan level sebelumnya untuk dibandingkan
        $previousLevel = $loyalty->level;
        $previousDiscountCode = $loyalty->discount_code;  // Simpan kode diskon sebelumnya
        
        // Cek level berdasarkan total_spent, tidak selalu mulai dari level 2
        if ($loyalty->total_spent >= 5000000) {
            if ($loyalty->level < 5) {
                Log::info("Upgrading to Level 5");
                $loyalty->level = 5;
                $loyalty->discount = 30;
            }
        } elseif ($loyalty->total_spent >= 2500000) {
            if ($loyalty->level < 4) {
                Log::info("Upgrading to Level 4");
                $loyalty->level = 4;
                $loyalty->discount = 25;
            }
        } elseif ($loyalty->total_spent >= 1000000) {
            if ($loyalty->level < 3) {
                Log::info("Upgrading to Level 3");
                $loyalty->level = 3;
                $loyalty->discount = 20;
            }
        } elseif ($loyalty->total_spent >= 500000) {
            if ($loyalty->level < 2) {
                Log::info("Upgrading to Level 2");
                $loyalty->level = 2;
                $loyalty->discount = 15;
            }
        } elseif ($loyalty->total_spent >= 100000) {
            if ($loyalty->level < 1) {
                Log::info("Upgrading to Level 1");
                $loyalty->level = 1;
                $loyalty->discount = 0;
            }
        }
    
        // Cek jika level naik
        if ($loyalty->level > $previousLevel) {
            // Jika levelnya 0, langsung generate kode diskon baru jika kode diskon kosong atau null
            if ($loyalty->level == 0 || !$loyalty->discount_code) {
                $loyalty->discount_code = $this->generateDiscountCode();
                Log::info("Generated new discount code: {$loyalty->discount_code}");
            }
            // Jika level naik dan kode diskon sebelumnya berbeda, generate kode diskon baru
            elseif ($loyalty->discount_code !== $previousDiscountCode) {
                $loyalty->discount_code = $this->generateDiscountCode();
                Log::info("Generated new discount code: {$loyalty->discount_code}");
            }
        }
    
        // Simpan perubahan
        $loyalty->save();
    }
    // Fungsi untuk menghasilkan kode diskon
    private function generateDiscountCode()
    {
        do {
            $code = 'DISCOUNT_' . strtoupper(Str::random(8)); // Menghasilkan 8 karakter acak
        } while (Loyalty::where('discount_code', $code)->exists());

        return $code;
    }

    // Fungsi untuk mendapatkan kode diskon ketika pemesanan pertama kali
    public function getDiscountCode(Request $request)
    {
        $user = $request->user();
        $loyalty = Loyalty::where('user_id', $user->id)->first();

        if (!$loyalty) {
            return response()->json([
                'success' => false,
                'message' => 'Loyalty data not found.'
            ], 404);
        }

        // Cek apakah user sudah pernah melakukan pemesanan sebelumnya
        $orderCount = Order::where('user_id', $user->id)->count();

        if ($orderCount === 0) {
            return response()->json([
                'success' => true,
                'discount_code' => $loyalty->discount_code,
            ]);
        } else {
            if ($loyalty->level >= 2) {
                return response()->json([
                    'success' => true,
                    'discount_code' => $loyalty->discount_code,
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada kode diskon.'
        ], 404);
    }

    // Fungsi untuk memperbarui kode diskon
    public function updateDiscountCode(Request $request)
    {
        $request->validate([
            'discount_code' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }

        $loyalty = Loyalty::where('user_id', $user->id)->first();

        if (!$loyalty) {
            return response()->json([
                'success' => false,
                'message' => 'Loyalty data not found.'
            ], 404);
        }

        $loyalty->discount_code = $request->discount_code;
        $loyalty->save();

        return response()->json([
            'success' => true,
            'message' => 'Discount code updated successfully.'
        ]);
    }
}
