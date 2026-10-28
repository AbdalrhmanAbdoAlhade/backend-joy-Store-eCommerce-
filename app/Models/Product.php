<?php
// app/Models/Product.php
namespace App\Models;

use App\Enums\Gender;
use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name', 'slug', 'sku', 'barcode', 'description', 'short_description',
        'brand_id', 'age_group_id', 'gender',
        'material', 'dimensions', 'weight', 'battery_required', 'batteries_included',
        'safety_warnings', 'choking_hazard', 'certifications', 'pieces_count',
        'educational', 'how_to_play',
        'price', 'sale_price', 'cost_price', 'tax_rate',
        'status', 'is_featured', 'is_new_arrival',
        'meta_title', 'meta_description',
        'views_count', 'sold_count', 'avg_rating', 'reviews_count',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'status' => ProductStatus::class,
            'battery_required' => 'boolean',
            'batteries_included' => 'boolean',
            'choking_hazard' => 'boolean',
            'educational' => 'boolean',
            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'certifications' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'weight' => 'decimal:3',
            'avg_rating' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'sku', 'price', 'sale_price', 'status', 'cost_price'])
            ->logOnlyDirty();
    }

    // Relations
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class)->orderBy('sort_order');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function relations(): HasMany
    {
        return $this->hasMany(ProductRelation::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_relations', 'product_id', 'related_product_id')
            ->wherePivot('type', 'related')
            ->withPivot('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class);
    }

    // Accessors
    public function getFinalPriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    public function getDiscountPercentageAttribute(): ?float
    {
        if (!$this->is_on_sale) {
            return null;
        }
        return round((($this->price - $this->sale_price) / $this->price) * 100, 1);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', ProductStatus::Active);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNewArrival($query)
    {
        return $query->where('is_new_arrival', true);
    }

    public function scopeInStock($query)
    {
        return $query->whereHas('variants', fn ($q) => $q->where('stock', '>', 0)->where('is_active', true));
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
    }
}
