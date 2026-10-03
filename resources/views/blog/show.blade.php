@extends('layouts.app')
@section('title', $post->getLocalTitle() . ' - urca')
@section('description', $post->getLocalExcerpt())

@section('content')

    {{-- Hero --}}
    <div class="blog-show-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <span class="blog-hero-cat">{{ $post->category }}</span>
                    <h1 class="text-white fw-bold mt-3 mb-4" style="font-size:clamp(1.5rem,3.5vw,2.5rem);line-height:1.3;">
                        {{ $post->getLocalTitle() }}
                    </h1>
                    <div class="d-flex flex-wrap gap-3 align-items-center" style="font-size:.875rem;">
                        <span class="blog-meta-item"><i class="fas fa-user"></i>{{ $post->author }}</span>
                        @if ($post->author_role)
                            <span class="blog-meta-item"><i class="fas fa-briefcase"></i>{{ $post->author_role }}</span>
                        @endif
                        <span class="blog-meta-item"><i
                                class="fas fa-calendar-alt"></i>{{ $post->published_at?->format('d M Y') }}</span>
                        <span class="blog-meta-item"><i class="fas fa-clock"></i>{{ $post->read_time }}
                            {{ __('app.home_blog_min_read') }}</span>
                        <span class="blog-meta-item"><i class="fas fa-eye"></i>{{ number_format($post->views) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breadcrumb --}}
    <div class="bg-white border-bottom py-2">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.875rem;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"
                            class="text-primary text-decoration-none">{{ __('app.nav_home') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('blog.index') }}"
                            class="text-primary text-decoration-none">{{ __('app.nav_blog') }}</a></li>
                    <li class="breadcrumb-item active text-muted">{{ Str::limit($post->getLocalTitle(), 50) }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="py-5" style="background:#f8fafc;">
        <div class="container">
            <div class="row g-4">

                {{-- Main Article --}}
                <div class="col-lg-8">
                    <article class="blog-article">

                        {{-- Cover image placeholder --}}
                        <div class="article-cover mb-5">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <i class="fas fa-tv text-white opacity-25" style="font-size:8rem;"></i>
                            </div>
                        </div>

                        {{-- Excerpt / Lead --}}
                        <p class="article-lead">{{ $post->getLocalExcerpt() }}</p>

                        {{-- Content --}}
                        <div class="article-content">
                            {!! nl2br(e($post->getLocalContent())) !!}
                        </div>

                        {{-- Tags --}}
                        @if ($post->tags)
                            <div class="article-tags mt-5 pt-4 border-top">
                                <span class="text-muted fw-semibold me-2" style="font-size:.875rem;">
                                    <i class="fas fa-tags text-primary me-1"></i>
                                    {{ app()->getLocale() === 'ar' ? 'الوسوم:' : 'Tags:' }}
                                </span>
                                @foreach ($post->tags as $tag)
                                    <a href="{{ route('blog.index', ['search' => $tag]) }}"
                                        class="blog-tag-link">{{ $tag }}</a>
                                @endforeach
                            </div>
                        @endif

                        {{-- Share --}}
                        <div class="article-share mt-4 pt-4 border-top">
                            <span class="text-muted fw-semibold me-3" style="font-size:.875rem;">
                                {{ app()->getLocale() === 'ar' ? 'شارك المقال:' : 'Share:' }}
                            </span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                target="_blank" rel="noopener" class="share-btn share-fb" aria-label="Share on Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->getLocalTitle()) }}&url={{ urlencode(url()->current()) }}"
                                target="_blank" rel="noopener" class="share-btn share-tw" aria-label="Share on Twitter/X">
                                <i class="fab fa-x-twitter"></i>
                            </a>
                            <a href="https://wa.me/?text={{ urlencode($post->getLocalTitle() . ' ' . url()->current()) }}"
                                target="_blank" rel="noopener" class="share-btn share-wa" aria-label="Share on WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <button
                                onclick="navigator.clipboard.writeText('{{ url()->current() }}').then(()=>showToast('{{ app()->getLocale() === 'ar' ? 'تم نسخ الرابط' : 'Link copied!' }}'))"
                                class="share-btn share-copy" aria-label="Copy link">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>

                    </article>

                    {{-- Back button --}}
                    <div class="mt-4">
                        <a href="{{ route('blog.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ app()->getLocale() === 'ar' ? 'العودة للمدونة' : 'Back to Blog' }}
                        </a>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-4">

                    {{-- About author card --}}
                    <div class="sidebar-card mb-4">
                        <div class="text-center p-4">
                            <div class="author-avatar mx-auto mb-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">{{ $post->author }}</h6>
                            @if ($post->author_role)
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 mb-2"
                                    style="border-radius:50px;font-size:.8rem;">{{ $post->author_role }}</span>
                            @endif
                            <p class="text-secondary small mb-0">
                                {{ app()->getLocale() === 'ar' ? 'خبير تقني ومتخصص في ملحقات وإلكترونيات الكمبيوتر' : 'Tech and computer accessories specialist at urca  ' }}
                            </p>
                        </div>
                    </div>

                    {{-- Related posts --}}
                    @if ($related->count() > 0)
                        <div class="sidebar-card">
                            <div class="sidebar-card-header">
                                <i class="fas fa-newspaper text-primary me-2"></i>
                                {{ app()->getLocale() === 'ar' ? 'مقالات ذات صلة' : 'Related Articles' }}
                            </div>
                            <div class="p-3">
                                @foreach ($related as $rel)
                                    <a href="{{ route('blog.show', $rel->id) }}"
                                        class="related-post-item d-flex gap-3 text-decoration-none {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                                        <div class="related-post-icon flex-shrink-0">
                                            <i class="fas fa-pen-nib"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <h6 class="fw-semibold text-dark mb-1 related-post-title">
                                                {{ Str::limit($rel->getLocalTitle(), 55) }}
                                            </h6>
                                            <div class="d-flex gap-2 text-muted" style="font-size:.75rem;">
                                                <span><i class="fas fa-clock me-1"></i>{{ $rel->read_time }} min</span>
                                                <span>{{ $rel->published_at?->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Categories in blog --}}
                    <div class="sidebar-card mt-4">
                        <div class="sidebar-card-header">
                            <i class="fas fa-folder text-primary me-2"></i>
                            {{ app()->getLocale() === 'ar' ? 'تصفح حسب الفئة' : 'Browse by Category' }}
                        </div>
                        <div class="p-3">
                            <a href="{{ route('blog.index') }}" class="sidebar-cat-link">
                                <i class="fas fa-th-large me-2 text-primary"></i>
                                {{ app()->getLocale() === 'ar' ? 'جميع المقالات' : 'All Articles' }}
                            </a>
                            @php
                                $blogCats = \App\Models\BlogPost::active()
                                    ->select('category')
                                    ->distinct()
                                    ->pluck('category');
                            @endphp
                            @foreach ($blogCats as $cat)
                                <a href="{{ route('blog.index', ['search' => $cat]) }}"
                                    class="sidebar-cat-link {{ $post->category === $cat ? 'active' : '' }}">
                                    <i class="fas fa-tag me-2 text-primary"></i>{{ $cat }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

@endsection

@push('styles')
    <style>
        /* ── Hero ── */
        .blog-show-hero {
            background: linear-gradient(135deg, #0f172a 0%, #030f1f 60%, #051836 100%);
            padding: 4rem 0 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .blog-show-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 80% 50%, rgba(37, 99, 235, .3) 0%, transparent 60%);
        }

        .blog-hero-cat {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, .15);
            backdrop-filter: blur(10px);
            color: #fff;
            font-size: .8rem;
            font-weight: 700;
            padding: .35rem 1rem;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, .25);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .blog-meta-item {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: rgba(255, 255, 255, .75);
            background: rgba(255, 255, 255, .08);
            padding: .3rem .8rem;
            border-radius: 50px;
            font-size: .8125rem;
        }

        .blog-meta-item i {
            color: rgba(255, 255, 255, .6);
            font-size: .85em;
        }

        /* ── Article ── */
        .blog-article {
            background: #fff;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .07);
            border: 1px solid #e2e8f0;
        }

        .article-cover {
            height: 320px;
            background: linear-gradient(135deg, #0f172a 0%, #051836 100%);
            border-radius: 14px;
            overflow: hidden;
            position: relative;
        }

        .article-lead {
            font-size: 1.125rem;
            color: #334155;
            line-height: 1.9;
            font-weight: 500;
            border-left: 4px solid #051836;
            padding-left: 1.25rem;
            margin-bottom: 2rem;
        }

        [dir="rtl"] .article-lead {
            border-left: none;
            border-right: 4px solid #051836;
            padding-left: 0;
            padding-right: 1.25rem;
        }

        .article-content {
            font-size: 1.0625rem;
            color: #475569;
            line-height: 2;
        }

        .article-content p {
            margin-bottom: 1.25rem;
        }

        /* Tags & Share */
        .blog-tag-link {
            display: inline-block;
            background: #e8edf5;
            color: #051836;
            font-size: .8125rem;
            font-weight: 600;
            padding: .3rem .85rem;
            border-radius: 50px;
            border: 1px solid #c3d0e3;
            text-decoration: none;
            margin: .2rem .2rem 0 0;
            transition: all .2s ease;
        }

        .blog-tag-link:hover {
            background: #051836;
            color: #fff;
            border-color: #051836;
        }

        .share-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            font-size: .9375rem;
            text-decoration: none;
            transition: all .25s ease;
            border: none;
            cursor: pointer;
            margin-right: .5rem;
        }

        .share-fb {
            background: #1877f2;
            color: #fff;
        }

        .share-tw {
            background: #000;
            color: #fff;
        }

        .share-wa {
            background: #25d366;
            color: #fff;
        }

        .share-copy {
            background: #f1f5f9;
            color: #475569;
        }

        .share-btn:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, .2);
        }

        /* ── Sidebar ── */
        .sidebar-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            border: 1px solid #e2e8f0;
        }

        .sidebar-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
            font-weight: 700;
            font-size: .9375rem;
            color: #0f172a;
        }

        .author-avatar {
            width: 72px;
            height: 72px;
            background: #e8edf5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #051836;
        }

        .related-post-item {
            transition: all .25s ease;
        }

        .related-post-item:hover .related-post-title {
            color: #051836;
        }

        .related-post-icon {
            width: 42px;
            height: 42px;
            background: #e8edf5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #051836;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .related-post-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
            transition: color .2s ease;
        }

        .sidebar-cat-link {
            display: flex;
            align-items: center;
            padding: .6rem .75rem;
            border-radius: 10px;
            text-decoration: none;
            color: #475569;
            font-size: .9rem;
            font-weight: 500;
            transition: all .2s ease;
            margin-bottom: .25rem;
        }

        .sidebar-cat-link:hover,
        .sidebar-cat-link.active {
            background: #e8edf5;
            color: #051836;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .blog-article {
                padding: 1.5rem;
            }

            .article-cover {
                height: 220px;
            }
        }
    </style>
@endpush
