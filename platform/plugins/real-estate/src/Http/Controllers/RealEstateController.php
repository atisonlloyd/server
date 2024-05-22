<?php

namespace Botble\RealEstate\Http\Controllers;

use Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\RealEstate\Http\Requests\UpdateSettingsRequest;
use Botble\RealEstate\Repositories\Interfaces\CurrencyInterface;
use Botble\RealEstate\Services\StoreCurrenciesService;
use Botble\Setting\Supports\SettingStore;

class RealEstateController extends BaseController
{
    protected CurrencyInterface $currencyRepository;

    public function __construct(CurrencyInterface $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function getSettings()
    {
        page_title()->setTitle(trans('plugins/real-estate::real-estate.settings'));

        Assets::addScripts(['jquery-ui'])
            ->addScriptsDirectly([
                'vendor/core/plugins/real-estate/js/currencies.js',
            ])
            ->addStylesDirectly([
                'vendor/core/plugins/real-estate/css/currencies.css',
            ]);

        $currencies = $this->currencyRepository
            ->getAllCurrencies()
            ->toArray();

        return view('plugins/real-estate::settings.index', compact('currencies'));
    }

    public function postSettings(
        UpdateSettingsRequest $request,
        BaseHttpResponse $response,
        StoreCurrenciesService $service,
        SettingStore $settingStore
    ) {
        foreach ($request->except(['_token', 'currencies', 'deleted_currencies']) as $settingKey => $settingValue) {
            if (is_array($settingValue)) {
                $settingValue = json_encode(array_filter($settingValue));
            }

            $settingStore->set($settingKey, $settingValue);
        }

        $settingStore->save();

        $currencies = json_decode($request->input('currencies'), true) ?: [];

        if (! $currencies) {
            return $response
                ->setNextUrl(route('real-estate.settings'))
                ->setError()
                ->setMessage(trans('plugins/real-estate::currency.require_at_least_one_currency'));
        }

        $deletedCurrencies = json_decode($request->input('deleted_currencies', []), true) ?: [];

        $service->execute($currencies, $deletedCurrencies);

        return $response
            ->setNextUrl(route('real-estate.settings'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
