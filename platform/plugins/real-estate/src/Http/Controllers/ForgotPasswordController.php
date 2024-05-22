<?php

namespace Botble\RealEstate\Http\Controllers;

use App\Http\Controllers\Controller;
use Botble\ACL\Traits\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use RealEstateHelper;
use SeoHelper;
use Theme;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function showLinkRequestForm()
    {
        if (! RealEstateHelper::isRegisterEnabled()) {
            abort(404);
        }

        SeoHelper::setTitle(trans('plugins/real-estate::account.forgot_password'));

        if (view()->exists(Theme::getThemeNamespace() . '::views.real-estate.account.auth.passwords.email')) {
            return Theme::scope('real-estate.account.auth.passwords.email')->render();
        }

        return view('plugins/real-estate::account.auth.passwords.email');
    }

    public function broker()
    {
        return Password::broker('accounts');
    }
}
