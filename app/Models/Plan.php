<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'code', 'price', 'gst_rate', 'credits', 'features', 'permissions', 'is_active', 'sort',
    ];

    protected $casts = [
        'features'    => 'array',
        'permissions' => 'array',
        'is_active'   => 'boolean',
    ];

    /** All module-access keys a plan can grant. */
    public const MODULES = [
        'reviews'      => 'Reviews & Replies',
        'gbp_content'  => 'Posts & Photos',
        'ai_media'     => 'AI Generated Media',
        'audit'        => 'Google Audit',
        'competitors'  => 'Competitor Analysis',
        'rank_checker' => 'Rank Checker',
        'social'       => 'Social Posting',
        'whatsapp'     => 'WhatsApp',
        'leads'        => 'Leads CRM',
        'keywords'     => 'Keyword Ideas',
        'invoicing'    => 'Invoicing & Tally',
        'ai_mode'      => 'AI Marketing Chat',
    ];

    /** GST-inclusive price → base (pre-GST) amount. */
    public function baseAmount(): float
    {
        return round($this->price / (1 + $this->gst_rate / 100), 2);
    }

    /** GST portion of the inclusive price. */
    public function gstAmount(): float
    {
        return round($this->price - $this->baseAmount(), 2);
    }

    public function allows(string $module): bool
    {
        // No permission list set → allow everything (backward compatible).
        if (empty($this->permissions)) {
            return true;
        }
        return in_array($module, $this->permissions, true);
    }
}
