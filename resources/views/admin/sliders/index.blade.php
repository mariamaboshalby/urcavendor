@extends('admin.layouts.app')

@section('title', 'صور السلايدر')
@section('page-title', 'صور السلايدر')
@section('breadcrumb')
    <i class="fas fa-chevron-left"></i>
    <span>صور السلايدر</span>
@endsection

@section('content')
    <div class="settings-container">
        <div class="card mb-4">
            <div class="card-header">
                <h2><i class="fas fa-images" style="color:var(--primary)"></i> إدارة صور السلايدر الرئيسي</h2>
            </div>
            <form action="{{ route('admin.sliders.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <p class="text-muted">أضف صورًا جديدة أو حدد الشرائح التي تريد حذفها.</p>
                    @if(count($sliderImages))
                        <div class="row g-3 mb-4">
                            @foreach($sliderImages as $index => $slide)
                                @php
                                    $previewPath = $slide['desktop'] ?? '';
                                    $previewUrl = str_starts_with($previewPath, 'sliders/')
                                        ? asset('storage/' . $previewPath)
                                        : asset($previewPath);
                                @endphp
                                <div class="col-6 col-lg-3">
                                    <div class="border rounded p-2 h-100">
                                        <img src="{{ $previewUrl }}" alt="الشريحة {{ $index + 1 }}" class="w-100 rounded mb-2" style="height:160px;object-fit:cover">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="remove_slider_images[]" value="{{ $index }}">
                                            حذف هذه الشريحة
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="form-group">
                        <label class="form-label" for="slider_images">إضافة صور</label>
                        <input type="file" id="slider_images" name="slider_images[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                        <small class="form-hint">يمكن اختيار حتى 10 صور في المرة الواحدة، بحد أقصى 5 ميجابايت للصورة.</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> حفظ صور السلايدر
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection