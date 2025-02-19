<?php

namespace Botble\RealEstate\Models;

use Botble\Base\Models\BaseModel;
use Botble\RealEstate\Enums\ModerationStatusEnum;
use Botble\RealEstate\Enums\PropertyPeriodEnum;
use Botble\RealEstate\Enums\PropertyStatusEnum;
use Botble\RealEstate\Enums\PropertyTypeEnum;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use RvMedia;
use Illuminate\Support\Str;

class Property extends BaseModel
{
    protected $table = 're_properties';

    protected $fillable = [
        'name',
        'type',
        'description',
        'content',
        'location',
        'images',
        'project_id',
        'number_bedroom',
        'number_bathroom',
        'number_floor',
        'square',
        'price',
        'status',
        'is_featured',
        'currency_id',
        'city_id',
        'state_id',
        'country_id',
        'period',
        'author_id',
        'author_type',
        'expire_date',
        'auto_renew',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'status' => PropertyStatusEnum::class,
        'moderation_status' => ModerationStatusEnum::class,
        'type' => PropertyTypeEnum::class,
        'period' => PropertyPeriodEnum::class,
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'expire_date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id')->withDefault();
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 're_property_features', 'property_id', 'feature_id');
    }

    public function facilities(): BelongsToMany
    {
        return $this->morphToMany(Facility::class, 'reference', 're_facilities_distances')->withPivot('distance');
    }

    public function getImagesAttribute($value): array
    {
        try {
            if ($value === '[null]') {
                return [];
            }

            $images = json_decode((string)$value, true);

            if (is_array($images)) {
                $images = array_filter($images);
            }

            return $images ?: [];
        } catch (Exception) {
            return [];
        }
    }

    public function getImageAttribute(): ?string
    {
        return Arr::first($this->images) ?? null;
    }

    public function getSquareTextAttribute(): string
    {
        return number_format($this->square) . ' ' . setting('real_estate_square_unit', 'm²');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function getAddressAttribute(): ?string
    {
        return $this->location;
    }

    public function author(): MorphTo
    {
        return $this->morphTo()->withDefault();
    }

    public function getCategoryAttribute(): Category
    {
        return $this->categories->first() ?: new Category();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 're_property_categories');
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('expire_date', '>=', now()->toDateTimeString())
                ->orWhere('never_expired', true);
        });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('expire_date', '<', now()->toDateTimeString())
                ->where('never_expired', false);
        });
    }

    public function getCityNameAttribute(): string
    {
        return $this->city->name . ', ' . $this->city->state->name;
    }

    public function getTypeHtmlAttribute(): ?string
    {
        return $this->type->label();
    }

    public function getStatusHtmlAttribute(): ?string
    {
        return $this->status->toHtml();
    }

    public function getCategoryNameAttribute(): ?string
    {
        return $this->category->name;
    }

    public function getImageThumbAttribute(): ?string
    {
        return $this->image ? RvMedia::getImageUrl($this->image, 'thumb', false, RvMedia::getDefaultImage()) : null;
    }

    public function getImageSmallAttribute(): ?string
    {
        return $this->image ? RvMedia::getImageUrl($this->image, 'small', false, RvMedia::getDefaultImage()) : null;
    }

    public function getPriceHtmlAttribute(): ?string
    {
        $price = $this->price_format;

        if ($this->type == PropertyTypeEnum::RENT) {
            $price .= ' / ' . Str::lower($this->period->label());
        }

        return $price;
    }

    public function getPriceFormatAttribute(): ?string
    {
        if ($this->price_formatted) {
            return $this->price_formatted;
        }

        $currency = $this->currency;

        if (! $currency || ! $currency->id) {
            $currency = get_application_currency();
        }

        return $this->price_formatted = format_price($this->price, $currency);
    }

    public function getMapIconAttribute(): string
    {
        return $this->type_html . ': ' . $this->price_format;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Property $property) {
            $property->categories()->detach();
        });
    }
}
