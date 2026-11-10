<x-layouts.app title="Page Not Found" :noindex="true">
    <section class="section">
        <div class="container-x max-w-xl text-center">
            <p class="gradient-text text-8xl font-black">404</p>
            <h1 class="mt-4 text-3xl font-extrabold">Page not found</h1>
            <p class="mt-4 text-slate-600 dark:text-slate-400">The page you're looking for doesn't exist or has been moved.</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('home') }}" class="btn-primary">Go Home</a>
                <a href="{{ route('products.index') }}" class="btn-outline">Browse Products</a>
            </div>
        </div>
    </section>
</x-layouts.app>
