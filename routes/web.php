<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Catalog
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/hosting', HostingController::class)->name('hosting');
Route::get('/domains', [DomainController::class, 'index'])->name('domains.index');

// Services & work
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{portfolio:slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed', [BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// Company pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/page/{page:slug}', [PageController::class, 'show'])->name('pages.show');

// Leads: contact, quote, order requests
Route::get('/contact', [InquiryController::class, 'contact'])->name('contact');
Route::get('/get-a-quote', [InquiryController::class, 'quote'])->name('quote');
Route::get('/order', [InquiryController::class, 'order'])->name('order');
Route::post('/inquiry', [InquiryController::class, 'store'])->middleware('throttle:6,1')->name('inquiries.store');
Route::get('/thank-you', [InquiryController::class, 'thanks'])->name('inquiries.thanks');
Route::post('/subscribe', SubscriberController::class)->middleware('throttle:6,1')->name('subscribe');

// Payment hub: hosted checkout for apps (see docs/payment-gateway-integration.md)
Route::match(['get', 'post'], '/pay/return/{attempt}/{result}', [CheckoutController::class, 'epsReturn'])
    ->whereIn('result', ['success', 'fail', 'cancel'])
    ->name('checkout.eps-return');
Route::get('/pay/{payment}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/pay/{payment}', [CheckoutController::class, 'pay'])->middleware('throttle:10,1')->name('checkout.pay');
Route::post('/pay/{payment}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
