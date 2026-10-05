<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>SLW Super Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet" />
        <link href="{{ asset('public/assets/css/style.css') }}" rel="stylesheet" />
    </head>
    <body>
        <main class="login-page" id="loginPage">
            <section class="login-brand-panel">
                <div class="brand-mark large">
                <span>SLW</span>
                <small>TRAVEL</small>
                </div>
                <div class="brand-copy">
                <span class="eyebrow"> <i class="bi bi-shield-check"></i> Secure Super Admin </span>
                <h1>One command center.<br /><em>Complete control.</em></h1>
                <p>Manage finance, bookings, agents, suppliers and portal content from one secure workspace.</p>
                </div>
                <div class="login-stat-row">
                <div>
                    <strong>24/7</strong>
                    <span>Portal oversight</span>
                </div>
                <div>
                    <strong>100%</strong>
                    <span>Secure access</span>
                </div>
                <div>
                    <strong>Live</strong>
                    <span>Business insights</span>
                </div>
                </div>
            </section>
            <section class="login-form-panel">
                <form class="login-card" id="loginForm" method="POST" action="{{ route('submit.login') }}">
                @csrf
                <div class="mobile-brand brand-mark">
                    <span>SLW</span>
                    <small>TRAVEL</small>
                </div>
                <span class="login-icon">
                    <i class="bi bi-lock"></i>
                </span>
                <h2>Welcome back</h2>
                <p>Sign in to access the Super Admin panel.</p>
                <label>Email address</label>
                <div class="field">
                    <i class="bi bi-person"></i>
                    <input type="email" name="adm_email" value="{{ old('adm_email') }}" placeholder="admin@slw.travel" />
                </div>
                @error('adm_email')
                <span class="text-danger">{{ $message }}</span>
                @enderror
                <div class="label-line">
                    <label>Password</label>
                    <a href="#">Forgot password?</a>
                </div>
                <div class="field">
                    <i class="bi bi-lock"></i>
                    <input id="password" type="password" name="adm_password" />
                    <button type="button" id="showPassword">Show</button>
                </div>
                @error('adm_password')
                <span class="text-danger">{{ $message }}</span>
                @enderror
                <label class="remember">
                    <input type="checkbox" checked />
                    Keep me signed in
                </label>
                <button class="login-button" type="submit">Sign in to Dashboard</button>
                <p class="security-note">
                    <i class="bi bi-shield-check"></i>
                    Protected by secure admin authentication
                </p>
                </form>
            </section>
        </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.getElementById("showPassword").addEventListener("click", function () {
                const password = document.getElementById("password");
                if (password.type === "password") {
                password.type = "text";
                this.innerHTML = "Hide";
                } else {
                password.type = "password";
                this.innerHTML = "Show";
                }
            });
        </script>
    </body>
</html>
