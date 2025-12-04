<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Nette\Schema\ValidationException;

class UserController extends Controller
{
    public function update(Request $request)
    {
        try{
            $user = $request->user();

            $request->validate([
                'name'   => 'required|string|max:255',
                'avatar' => 'nullable|image|max:2048', // Max 2MB
            ]);
           // Log::info('REQUEST DATA', $request->all());


        // 1. Xử lý Avatar
            if ($request->hasFile('avatar')) {
                // Xóa ảnh cũ:
                // Vì chúng ta sẽ lưu đường dẫn tương đối (avatars/xyz.jpg),
                // việc xóa trở nên cực kỳ đơn giản và chính xác.
                if ($user->imgurl && Storage::disk('public')->exists($user->imgurl)) {
                    Storage::disk('public')->delete($user->imgurl);
                }

                // Upload ảnh mới và lấy đường dẫn tương đối
                $path = $request->file('avatar')->store('avatars', 'public');

                // CHỈ LƯU: avatars/ten_file_hash.jpg
                $user->imgurl = $path;
            }

            // 2. Update thông tin khác
            if ($request->filled('name')) {
                $user->name = $request->name;
            }

            $user->save();

            // Trả về dữ liệu, lúc này Accessor ở Bước 2 sẽ tự động tạo full URL
            return response()->json([
                'message' => 'User updated successfully',
                'data'    => $user
            ]);
        }
        catch (ValidationException $e){
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ],422);
        }
        catch (\Exception $exception){
            return response()->json([
                'message' => "Lỗi" . $exception->getMessage(),
                'data'    => $user
            ]);
        }

    }

}
