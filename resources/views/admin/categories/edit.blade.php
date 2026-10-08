@extends('layouts.admin.app')
@section('title', $title)
@section('content')
    <div >
        <div >
            <div class="page-header">
                <div class="page-block mb-3">
    <div class="row align-items-center">
        <div class="col-md-12">
            <div class="page-header-title">
                <h2 class="mb-0">Chỉnh sửa danh mục game</h2>
                <p class="text-muted">Cập nhật thông tin danh mục game</p>
            </div>
        </div>
    </div>
</div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="{{ old('name', $category->name) }}"
                                        class="form-control @error('name') is-invalid @enderror" placeholder="Nhập tên danh mục sản phẩm">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                                        class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Danh mục mẹ (Game Group)</label>
                                    <select name="game_group_id" class="form-select @error('game_group_id') is-invalid @enderror">
                                        <option value="">-- Không có --</option>
                                        @foreach($gameGroups as $group)
                                            <option value="{{ $group->id }}" {{ old('game_group_id', $category->game_group_id) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('game_group_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Nền tảng</label>
                                    <input list="platforms" type="text" name="platform" value="{{ old('platform', $category->platform) }}"
                                        class="form-control @error('platform') is-invalid @enderror"
                                        placeholder="VD: LOL">
                                    <datalist id="platforms">
                                        <option value="LOL">
                                        <option value="PUBG">
                                        <option value="LMHT">
                                        <option value="Free Fire">
                                        <option value="Valorant">
                                    </datalist>
                                    @error('platform')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-12 d-flex align-items-center mb-3">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="active" id="active" value="1" {{ old('active', $category->active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="active">Kích hoạt hiển thị</label>
                                </div>
                                @error('active')
                                    <div class="invalid-feedback d-block ms-3">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mt-2 mb-3">
                                <h6 class="fw-bold border-bottom pb-2">Cài đặt Flash Sale (Tùy chọn)</h6>
                            </div>
                            
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label d-block">Có phải Flash Sale?</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_flash_sale" id="is_flash_sale" value="1" {{ old('is_flash_sale', $category->is_flash_sale) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_flash_sale">Bật Flash Sale</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Giá cũ (Sale)</label>
                                    <input type="number" name="flash_sale_old_price" class="form-control" value="{{ old('flash_sale_old_price', $category->flash_sale_old_price) }}" placeholder="VD: 500000">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Giá mới (Sale)</label>
                                    <input type="number" name="flash_sale_new_price" class="form-control" value="{{ old('flash_sale_new_price', $category->flash_sale_new_price) }}" placeholder="VD: 250000">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian kết thúc Sale</label>
                                    <input type="datetime-local" name="flash_sale_end_time" class="form-control" value="{{ old('flash_sale_end_time', $category->flash_sale_end_time ? \Carbon\Carbon::parse($category->flash_sale_end_time)->format('Y-m-d\TH:i') : '') }}">
                                </div>
                            </div>

                            <div class="col-12 mt-2 mb-3">
                                <h6 class="fw-bold border-bottom pb-2">Hình ảnh</h6>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Ảnh đại diện</label>
                                    <div class="image-upload" style="position: relative; border: 2px dashed #4680ff; background: rgba(70, 128, 255, 0.05); padding: 20px; border-radius: 8px; text-align: center; min-height: 140px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                        <input type="file" name="thumbnail" id="input_thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 5;">
                                        <div id="preview_thumbnail_box" style="pointer-events: none;">
                                            @if($category->thumbnail)
                                                <img id="preview_thumbnail_img" src="{{ asset($category->thumbnail) }}" alt="img" style="max-height: 90px; max-width: 100%; object-fit: contain; margin-bottom: 8px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                                <div id="preview_thumbnail_name" class="fw-semibold text-primary small">Đổi ảnh đại diện (Kéo thả hoặc click)</div>
                                            @else
                                                <img id="preview_thumbnail_img" src="" style="max-height: 90px; max-width: 100%; border-radius: 6px; display: none; margin-bottom: 8px;">
                                                <div id="preview_thumbnail_name" class="fw-semibold text-primary small">Kéo thả hoặc click để tải ảnh lên</div>
                                            @endif
                                            <p class="text-muted small mt-1 mb-0">Hỗ trợ JPG, PNG, WEBP, GIF (Tối đa 10MB)</p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <input type="text" name="thumbnail_url" id="input_thumbnail_url" class="form-control form-control-sm" placeholder="Hoặc dán link ảnh mới: https://..." value="{{ old('thumbnail_url') }}">
                                    </div>
                                    @error('thumbnail')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Ảnh Tag (Mua Nhiều, Hot...)</label>
                                    <div class="image-upload" style="position: relative; border: 2px dashed #ffb822; background: rgba(255, 184, 34, 0.05); padding: 20px; border-radius: 8px; text-align: center; min-height: 140px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                        <input type="file" name="tag_image" id="input_tag_image" class="form-control @error('tag_image') is-invalid @enderror" accept="image/*" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 5;">
                                        <div id="preview_tag_image_box" style="pointer-events: none;">
                                            @if($category->tag_image)
                                                <img id="preview_tag_image_img" src="{{ asset($category->tag_image) }}" alt="img" style="max-height: 60px; max-width: 100%; object-fit: contain; margin-bottom: 8px; border-radius: 6px;">
                                                <div id="preview_tag_image_name" class="fw-semibold text-warning small">Đổi ảnh Tag (Kéo thả hoặc click)</div>
                                            @else
                                                <img id="preview_tag_image_img" src="" style="max-height: 60px; max-width: 100%; border-radius: 6px; display: none; margin-bottom: 8px;">
                                                <div id="preview_tag_image_name" class="fw-semibold text-warning small">Kéo thả ảnh Tag vào đây</div>
                                            @endif
                                            <p class="text-muted small mt-1 mb-0">Hỗ trợ PNG, WEBP trong suốt (Tối đa 10MB)</p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <input type="text" name="tag_image_url" id="input_tag_image_url" class="form-control form-control-sm" placeholder="Hoặc dán link ảnh Tag mới: https://..." value="{{ old('tag_image_url') }}">
                                    </div>
                                    @error('tag_image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Nhập mô tả chi tiết danh mục">{{ old('description', $category->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12 mt-3">
                                <button type="submit" class="btn btn-primary me-2">Cập nhật</button>
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function setupEditPreview(fileInputId, previewImgId, previewNameId, urlInputId) {
            const fileInput = document.getElementById(fileInputId);
            const previewImg = document.getElementById(previewImgId);
            const previewName = document.getElementById(previewNameId);
            const urlInput = document.getElementById(urlInputId);

            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(evt) {
                            previewImg.src = evt.target.result;
                            previewImg.style.display = 'inline-block';
                            previewName.textContent = '✓ ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
                            previewName.className = 'fw-semibold text-success small';
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            if (urlInput) {
                urlInput.addEventListener('input', function() {
                    const val = urlInput.value.trim();
                    if (val && (val.startsWith('http') || val.startsWith('data:'))) {
                        previewImg.src = val;
                        previewImg.style.display = 'inline-block';
                        previewName.textContent = '✓ Đang dùng link ảnh trực tiếp';
                        previewName.className = 'fw-semibold text-success small';
                    }
                });
            }
        }

        setupEditPreview('input_thumbnail', 'preview_thumbnail_img', 'preview_thumbnail_name', 'input_thumbnail_url');
        setupEditPreview('input_tag_image', 'preview_tag_image_img', 'preview_tag_image_name', 'input_tag_image_url');
    });
    </script>
@endsection
