@extends('portal::layouts.app')

@section('body')
<section>
    <div class="dotted"></div>
    <div class="container">
        <div class="badge">
            <p><x-tabler-star/>Good choice for the Future!</p>
        </div>
        <div class="hero">
            <div class="label">
                <h1>{{ Billmora::getGeneral('company_name') }}</h1>
                <p>{{ Billmora::getGeneral('company_description') }}</p>
            </div>
            <div class="action">
                <a class="btn btn-secondary" href="/client">Client Area</a>
                <a class="btn btn-primary" href="/store">Get Started Now</a>
            </div>
        </div>
        <div class="feature">
            <div class="card">
                <div class="label">
                    <div><x-tabler-clock/></div>
                    <h2>24/7 Support</h2>
                </div>
                <p class="description">
                    Get peace of mind with our expert support team available 24/7. Whether you need technical help or have questions, we're here to assist you at any time, day or night.
                </p>
            </div>
            <div class="card">
                <div class="label">
                    <div><x-tabler-screenshot/></div>
                    <h2>Scalable Resource</h2>
                </div>
                <p class="description">
                    Grow with ease—our hosting solutions scale with your needs. Add more storage, bandwidth, or processing power whenever you need it, without any downtime.
                </p>
            </div>
            <div class="card">
                <div class="label">
                    <div><x-tabler-cpu/></div>
                    <h2>Fast Performance</h2>
                </div>
                <p class="description">
                    Enjoy blazing-fast performance with our optimized servers. Whether it's a website, application, or database, we ensure minimal load times and a smooth experience for your users.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection