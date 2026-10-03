@extends('layouts.app')
@section('title', (app()->getLocale() === 'ar' ? 'اتصل بنا' : 'Contact Us') . ' - urca')

@section('content')

    {{-- Hero Header --}}
    <div style="background:linear-gradient(135deg,#0f172a 0%,#030f1f 100%);padding:2.5rem 0;">
        <div class="container">
            <h1 class="text-white fw-bold mb-1">{{ app()->getLocale() === 'ar' ? 'اتصل بنا' : 'Contact Us' }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"
                            class="text-white-50 text-decoration-none">{{ __('app.nav_home') }}</a></li>
                    <li class="breadcrumb-item active text-white-50">
                        {{ app()->getLocale() === 'ar' ? 'اتصل بنا' : 'Contact Us' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Contact Info Cards --}}
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4 mb-5">
                {{-- Phone --}}
                <div class="col-md-6 col-lg-3">
                    <div class="contact-card card border-0 shadow-sm h-100 text-center p-4">
                        <div class="contact-icon mx-auto mb-3">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h5 class="fw-bold mb-2" style="color:#0f172a;">
                            {{ app()->getLocale() === 'ar' ? 'اتصل بنا' : 'Call Us' }}</h5>
                        <a href="tel:+201093803270" class="text-primary text-decoration-none fw-semibold">+20 10
                            93803270</a>
                    </div>
                </div>

                {{-- WhatsApp --}}
                <div class="col-md-6 col-lg-3">
                    <div class="contact-card card border-0 shadow-sm h-100 text-center p-4">
                        <div class="contact-icon mx-auto mb-3" style="background:#e8f5e9;color:#25D366;">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h5 class="fw-bold mb-2" style="color:#0f172a;">WhatsApp</h5>
                        <a href="https://wa.me/201093803270" target="_blank" class="text-decoration-none fw-semibold"
                            style="color:#25D366;">{{ app()->getLocale() === 'ar' ? 'راسلنا الآن' : 'Message Us' }}</a>
                    </div>
                </div>

                {{-- Email --}}
                <div class="col-md-6 col-lg-3">
                    <div class="contact-card card border-0 shadow-sm h-100 text-center p-4">
                        <div class="contact-icon mx-auto mb-3">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5 class="fw-bold mb-2" style="color:#0f172a;">
                            {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</h5>
                        <a href="mailto:urca .tv@gmail.com" class="text-primary text-decoration-none fw-semibold"
                            style="font-size:.9rem;">urca .tv@gmail.com</a>
                    </div>
                </div>

                {{-- Working Hours --}}
                <div class="col-md-6 col-lg-3">
                    <div class="contact-card card border-0 shadow-sm h-100 text-center p-4">
                        <div class="contact-icon mx-auto mb-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5 class="fw-bold mb-2" style="color:#0f172a;">
                            {{ app()->getLocale() === 'ar' ? 'ساعات العمل' : 'Working Hours' }}</h5>
                        <p class="text-secondary mb-0 small">{{ __('app.footer_hours') }}</p>
                    </div>
                </div>
            </div>

            {{-- Map & Form Section --}}
            <div class="row g-5">
                {{-- Contact Form --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4">
                        <h3 class="fw-bold mb-4" style="color:#0f172a;">
                            <i class="fas fa-paper-plane text-primary me-2"></i>
                            {{ app()->getLocale() === 'ar' ? 'أرسل لنا رسالة' : 'Send Us a Message' }}
                        </h3>
                        <form action="#" method="POST" id="contactForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label
                                        class="form-label fw-semibold">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="form-label fw-semibold">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label
                                        class="form-label fw-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone' }}</label>
                                    <input type="tel" name="phone" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label
                                        class="form-label fw-semibold">{{ app()->getLocale() === 'ar' ? 'الموضوع' : 'Subject' }}</label>
                                    <input type="text" name="subject" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label
                                        class="form-label fw-semibold">{{ app()->getLocale() === 'ar' ? 'الرسالة' : 'Message' }}</label>
                                    <textarea name="message" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i
                                            class="fas fa-paper-plane me-2"></i>{{ app()->getLocale() === 'ar' ? 'إرسال الرسالة' : 'Send Message' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Map & Location Info --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h3 class="fw-bold mb-4" style="color:#0f172a;">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            {{ app()->getLocale() === 'ar' ? 'موقعنا' : 'Our Location' }}
                        </h3>
                        <div class="mb-4">
                            <div class="d-flex align-items-start gap-3 mb-3 p-3"
                                style="background:#f8fafc;border-radius:12px;">
                                <div class="flex-shrink-0">
                                    <div class="small-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#0f172a;">{{ __('app.footer_address') }}</h6>

                                </div>
                            </div>
                        </div>
                        <a href="https://www.google.com/maps/place/30%C2%B056'37.3%22N+31%C2%B017'22.0%22E/@30.9436857,31.2894227,21z"
                            target="_blank" class="btn btn-outline-primary mb-4">
                            <i
                                class="fas fa-directions me-2"></i>{{ app()->getLocale() === 'ar' ? 'احصل على الاتجاهات' : 'Get Directions' }}
                        </a>
                    </div>

                    {{-- Google Map --}}
                    <div class="map-container-contact">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d714.1338338154081!2d31.28942265912146!3d30.94368566303161!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2s!5e0!3m2!1sar!2seg!4v1783340972314!5m2!1sar!2seg"
                            width="100%" height="350" style="border:0;border-radius:16px;" allowfullscreen=""
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-5 text-center" style="background:linear-gradient(135deg,#030f1f 0%,#051836 100%);">
        <div class="container">
            <h2 class="text-white fw-bold mb-3">{{ app()->getLocale() === 'ar' ? 'هل لديك سؤال؟' : 'Have a Question?' }}
            </h2>
            <p class="text-white-50 mb-4" style="font-size:1.125rem;">
                {{ app()->getLocale() === 'ar' ? 'نحن هنا للمساعدة! تواصل معنا الآن' : "We're here to help! Contact us now" }}
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="tel:+201093803270" class="btn btn-light btn-lg px-5">
                    <i class="fas fa-phone-alt me-2"></i>{{ app()->getLocale() === 'ar' ? 'اتصل الآن' : 'Call Now' }}
                </a>
                <a href="https://wa.me/201093803270" target="_blank" class="btn btn-outline-light btn-lg px-5">
                    <i class="fab fa-whatsapp me-2"></i>WhatsApp
                </a>
            </div>
        </div>
    </section>

@endsection

@push('styles')
    <style>
        .contact-card {
            border-radius: 16px !important;
            transition: all .3s ease;
        }

        .contact-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, .12) !important;
        }

        .contact-icon {
            width: 70px;
            height: 70px;
            background: #e8edf5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #051836;
            transition: all .3s ease;
        }

        .contact-card:hover .contact-icon {
            background: #051836;
            color: #fff;
            transform: scale(1.1) rotate(5deg);
        }

        .small-icon {
            width: 40px;
            height: 40px;
            background: #e8edf5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #051836;
        }

        .map-container-contact {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .12);
            transition: all .3s ease;
        }

        .map-container-contact:hover {
            box-shadow: 0 16px 48px rgba(0, 0, 0, .18);
            transform: translateY(-4px);
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: .75rem 1rem;
            transition: all .3s ease;
        }

        .form-control:focus {
            border-color: #051836;
            box-shadow: 0 0 0 3px rgba(5, 24, 54, .1);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.getElementById('contactForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            alert(
                '{{ app()->getLocale() === 'ar' ? 'شكراً لتواصلك معنا! سنرد عليك قريباً.' : 'Thank you for contacting us! We will get back to you soon.' }}'
                );
            this.reset();
        });
    </script>
@endpush
