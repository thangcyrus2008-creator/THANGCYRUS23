<?php

return [
    'required' => 'Vui lòng cung cấp :attribute.',
    'string' => ':attribute phải là chuỗi ký tự.',
    'numeric' => ':attribute phải là số.',
    'integer' => ':attribute phải là số nguyên.',
    'image' => ':attribute phải là một tệp hình ảnh hợp lệ.',
    'mimes' => ':attribute phải là tệp thuộc định dạng: :values.',
    'max' => [
        'numeric' => ':attribute không được lớn hơn :max.',
        'file' => ':attribute không được vượt quá dung lượng :max KB.',
        'string' => ':attribute không được vượt quá :max ký tự.',
    ],
    'min' => [
        'numeric' => ':attribute không được nhỏ hơn :min.',
        'file' => ':attribute phải có dung lượng ít nhất :min KB.',
        'string' => ':attribute phải có ít nhất :min ký tự.',
    ],
    'unique' => ':attribute đã tồn tại trên hệ thống.',
    'exists' => ':attribute đã chọn không tồn tại.',
    'boolean' => ':attribute phải là true hoặc false.',
    'email' => ':attribute phải là email hợp lệ.',
    'confirmed' => 'Xác nhận :attribute không khớp.',

    'attributes' => [
        'name' => 'Tên danh mục',
        'thumbnail' => 'Ảnh đại diện',
        'tag_image' => 'Ảnh Tag',
        'thumb' => 'Ảnh đại diện',
        'images' => 'Hình ảnh chi tiết',
        'description' => 'Mô tả',
        'price' => 'Giá bán',
        'account_name' => 'Tài khoản',
        'password' => 'Mật khẩu',
        'platform' => 'Nền tảng',
        'game_group_id' => 'Nhóm game',
        'site_logo' => 'Logo website',
        'site_favicon' => 'Favicon website',
        'site_banner' => 'Banner website',
        'wheel_image' => 'Ảnh vòng quay',
    ],
];
