<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Http\Middleware\CheckAdminAccess;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard; 
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Navigation\MenuItem;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class NibiruPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('nibiru')
            ->path('nibiru')
            ->login(Login::class)
            ->brandName('Sehelai Nibiru')
            ->font('Poppins')
            ->colors([
                'primary' => Color::hex('#bcd5fb'),
                'success' => Color::hex('#bcd5fb'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckAdminAccess::class, 
            ])
            ->userMenuItems([
                'logout' => MenuItem::make()->label('Logout'),
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <style>
                        .nibiru-login-page {
                            background-color: #bcd5fb;
                        }

                        .nibiru-login-page .fi-simple-layout {
                            min-height: 100vh;
                            position: relative;
                            overflow: hidden;
                        }

                        .nibiru-login-page .fi-simple-layout::before {
                            content: "";
                            position: fixed;
                            inset: 0 58% 0 0;
                            background: linear-gradient(90deg, rgba(21, 58, 97, 0.92), rgba(77, 111, 153, 0.86));
                            border-right: 1px solid rgba(255, 255, 255, 0.28);
                            z-index: 0;
                        }

                        .nibiru-login-page .fi-simple-layout::after {
                            content: "";
                            position: fixed;
                            inset: 0 58% 0 0;
                            background-image: url("/images/auth/login-left.jpg");
                            background-repeat: no-repeat;
                            background-position: center;
                            background-size: min(78%, 720px) auto;
                            opacity: 0.34;
                            z-index: 0;
                            pointer-events: none;
                        }

                        .nibiru-login-page .fi-simple-main-ctn {
                            position: relative;
                            z-index: 1;
                            display: flex;
                            justify-content: flex-end;
                            min-height: 100vh;
                            padding: clamp(1rem, 4vw, 3rem);
                        }

                        .nibiru-login-page .fi-simple-main {
                            max-width: 540px;
                            width: 100%;
                            background: transparent !important;
                            box-shadow: none !important;
                        }

                        .nibiru-login-page .fi-simple-page {
                            background: #ffffff;
                            border: 1px solid #d7e3f6;
                            border-radius: 1.25rem;
                            padding: clamp(1.1rem, 2.5vw, 2rem);
                            box-shadow: 0 14px 35px rgba(26, 54, 93, 0.12);
                        }

                        .nibiru-login-page .fi-simple-page,
                        .nibiru-login-page .fi-simple-page * {
                            color: #111827;
                        }

                        .nibiru-login-page .fi-simple-page .fi-input-wrp,
                        .nibiru-login-page .fi-simple-page input,
                        .nibiru-login-page .fi-simple-page textarea,
                        .nibiru-login-page .fi-simple-page select {
                            background: #ffffff !important;
                            border-color: #cbd5e1 !important;
                            color: #111827 !important;
                        }

                        .nibiru-login-page .fi-simple-page .fi-input-wrp:focus-within {
                            border-color: #bcd5fb !important;
                            box-shadow: 0 0 0 1px #bcd5fb inset !important;
                        }

                        .nibiru-login-page .fi-simple-page input:focus,
                        .nibiru-login-page .fi-simple-page textarea:focus,
                        .nibiru-login-page .fi-simple-page select:focus {
                            outline: none !important;
                            border-color: #bcd5fb !important;
                            box-shadow: none !important;
                        }

                        .nibiru-login-page .fi-simple-page ::placeholder {
                            color: #6b7280 !important;
                        }

                        .nibiru-login-page .fi-simple-page button[type="submit"],
                        .nibiru-login-page .fi-simple-page .fi-btn {
                            background: #bcd5fb !important;
                            border-color: #9ebce8 !important;
                            color: #111827 !important;
                        }

                        .nibiru-login-page .fi-simple-page button[type="submit"]:hover,
                        .nibiru-login-page .fi-simple-page .fi-btn:hover {
                            background: #acc8f4 !important;
                        }

                        .nibiru-login-page .fi-simple-page input[type="checkbox"] {
                            accent-color: #bcd5fb;
                        }

                        .nibiru-login-page .fi-simple-page input[type="checkbox"]:checked {
                            border-color: #bcd5fb !important;
                            background-color: #bcd5fb !important;
                        }

                        .nibiru-login-page .fi-simple-page .fi-simple-header {
                            text-align: left;
                        }

                        @media (max-width: 1024px) {
                            .nibiru-login-page .fi-simple-layout::before {
                                display: none;
                            }

                            .nibiru-login-page .fi-simple-main-ctn {
                                justify-content: center;
                                padding: 1rem;
                            }
                        }
                    </style>
                ')
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <div class="hidden sm:flex items-center mr-4 text-sm font-medium text-gray-500 dark:text-gray-400">
                        👋 Welcome back,&nbsp;<strong class="text-primary-500">{{ auth()->user()->name }}</strong>
                    </div>
                ')
            )
            // KUNCI 3: Pake INLINE CSS biar kebal dari bug Tailwind!
            ->renderHook(
                \Filament\View\PanelsRenderHook::SIDEBAR_NAV_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <div style="padding: 1.5rem 1rem 1.5rem 1rem; margin-top: auto; width: 100%;">
                        <form method="POST" action="{{ filament()->getLogoutUrl() }}" style="width: 100%; margin: 0;">
                            @csrf
                            <button type="submit" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; color: #ef4444; background-color: rgba(239, 68, 68, 0.1); border-radius: 0.5rem; cursor: pointer; border: none; transition: all 0.2s;" onmouseover="this.style.backgroundColor=\'rgba(239, 68, 68, 0.2)\'" onmouseout="this.style.backgroundColor=\'rgba(239, 68, 68, 0.1)\'">
                                <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                <span style="font-family: inherit;">Logout</span>
                            </button>
                        </form>
                    </div>
                ')
            );
    }
}