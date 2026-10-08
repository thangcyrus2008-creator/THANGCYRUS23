<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RandomCategory;
use App\Helpers\UploadHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RandomCategoryController extends Controller
{
    /**
     * Đường dẫn thư mục lưu ảnh thumbnail
     */
    private const UPLOAD_DIR = 'random-categories';

    /**
     * Hiển thị danh sách danh mục random
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $title = "Danh sách danh mục random";
        $query = RandomCategory::orderBy('id', 'DESC');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 10);
        $categories = $query->paginate($perPage)->withQueryString();

        return view('admin.random-categories.index', compact('title', 'categories'));
    }

    /**
     * Hiển thị form tạo danh mục random mới
     */
    public function create()
    {
        $title = "Thêm danh mục random mới";
        $gameGroups = \App\Models\GameGroup::where('active', true)->get();
        return view('admin.random-categories.create', compact('title', 'gameGroups'));
    }

    /**
     * Lưu danh mục random mới vào database
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:random_categories,name',
            'thumbnail' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg|max:10240',
            'thumbnail_url' => 'nullable|string',
            'tag_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg|max:10240',
            'tag_image_url' => 'nullable|string',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'platform' => 'nullable|string|max:255',
            'game_group_id' => 'nullable|exists:game_groups,id',
            'is_flash_sale' => 'boolean',
            'flash_sale_old_price' => 'nullable|numeric|min:0',
            'flash_sale_new_price' => 'nullable|numeric|min:0',
            'flash_sale_end_time' => 'nullable|date',
        ]);

        if (!$request->hasFile('thumbnail') && empty($request->input('thumbnail_url'))) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['thumbnail' => 'Vui lòng chọn tải lên ảnh đại diện hoặc nhập link ảnh!']);
        }

        try {
            DB::beginTransaction();

            $data = $request->except(['thumbnail_url', 'tag_image_url']);
            $data['slug'] = Str::slug($request->name);
            $data['is_flash_sale'] = $request->has('is_flash_sale');

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = UploadHelper::upload($request->file('thumbnail'), self::UPLOAD_DIR);
            } elseif (!empty($request->input('thumbnail_url'))) {
                $data['thumbnail'] = trim($request->input('thumbnail_url'));
            }

            if ($request->hasFile('tag_image')) {
                $data['tag_image'] = UploadHelper::upload($request->file('tag_image'), self::UPLOAD_DIR);
            } elseif (!empty($request->input('tag_image_url'))) {
                $data['tag_image'] = trim($request->input('tag_image_url'));
            }

            RandomCategory::create($data);

            DB::commit();

            return redirect()->route('admin.random-categories.index')
                ->with('success', 'Danh mục random đã được thêm thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating random category: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Đã xảy ra lỗi khi thêm danh mục: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa danh mục random
     */
    public function edit(RandomCategory $category)
    {
        $title = 'Chỉnh sửa danh mục random';
        $gameGroups = \App\Models\GameGroup::where('active', true)->get();
        return view('admin.random-categories.edit', compact('title', 'category', 'gameGroups'));
    }

    /**
     * Cập nhật danh mục random
     */
    public function update(Request $request, RandomCategory $category)
    {
        $request->validate([
            'name' => 'required|string|unique:random_categories,name,' . $category->id,
            'thumbnail' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg|max:10240',
            'thumbnail_url' => 'nullable|string',
            'tag_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg|max:10240',
            'tag_image_url' => 'nullable|string',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'platform' => 'nullable|string|max:255',
            'game_group_id' => 'nullable|exists:game_groups,id',
            'is_flash_sale' => 'boolean',
            'flash_sale_old_price' => 'nullable|numeric|min:0',
            'flash_sale_new_price' => 'nullable|numeric|min:0',
            'flash_sale_end_time' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except(['thumbnail_url', 'tag_image_url']);
            if (!isset($data['active'])) {
                $data['active'] = false;
            }
            $data['slug'] = Str::slug($request->name);
            $data['is_flash_sale'] = $request->has('is_flash_sale');

            if ($request->hasFile('thumbnail')) {
                if ($category->thumbnail && !str_starts_with($category->thumbnail, 'data:')) {
                    UploadHelper::deleteByUrl($category->thumbnail);
                }
                $data['thumbnail'] = UploadHelper::upload($request->file('thumbnail'), self::UPLOAD_DIR);
            } elseif (!empty($request->input('thumbnail_url'))) {
                $data['thumbnail'] = trim($request->input('thumbnail_url'));
            }

            if ($request->hasFile('tag_image')) {
                if ($category->tag_image && !str_starts_with($category->tag_image, 'data:')) {
                    UploadHelper::deleteByUrl($category->tag_image);
                }
                $data['tag_image'] = UploadHelper::upload($request->file('tag_image'), self::UPLOAD_DIR);
            } elseif (!empty($request->input('tag_image_url'))) {
                $data['tag_image'] = trim($request->input('tag_image_url'));
            }

            $category->update($data);

            DB::commit();

            return redirect()->route('admin.random-categories.index')
                ->with('success', 'Danh mục random đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating random category: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Đã xảy ra lỗi khi cập nhật danh mục: ' . $e->getMessage());
        }
    }

    /**
     * Xóa danh mục random
     */
    public function destroy(RandomCategory $category)
    {
        try {
            DB::beginTransaction();

            // Kiểm tra xem có tài khoản nào thuộc danh mục này không
            if ($category->accounts()->count() > 0) {
                DB::rollBack();

                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể xóa danh mục này vì có tài khoản thuộc danh mục!'
                    ], 400);
                }

                return redirect()->route('admin.random-categories.index')
                    ->with('error', 'Không thể xóa danh mục này vì có tài khoản thuộc danh mục!');
            }

            // Delete thumbnail if exists
            if ($category->thumbnail) {
                UploadHelper::deleteByUrl($category->thumbnail);
            }

            if ($category->tag_image) {
                UploadHelper::deleteByUrl($category->tag_image);
            }

            $category->delete();

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Danh mục random đã được xóa thành công!'
                ]);
            }

            return redirect()->route('admin.random-categories.index')
                ->with('success', 'Danh mục random đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting random category: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục random. Lỗi: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.random-categories.index')
                ->with('error', 'Không thể xóa danh mục random. Lỗi: ' . $e->getMessage());
        }
    }
}
