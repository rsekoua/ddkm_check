<?php

namespace App\Rules;

use App\Models\Distribution;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueMonthlyDistributionRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    protected $siteId;
    protected $deliveryTypeId;
    protected $distributionId;

    public function __construct($siteId, $deliveryTypeId, $distributionId = null)
    {
        $this->siteId = $siteId;
        $this->deliveryTypeId = $deliveryTypeId;
        $this->distributionId = $distributionId;
    }



    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value || !$this->siteId || !$this->deliveryTypeId) {
            return;
        }

        $date = \Carbon\Carbon::parse($value);

        $query = Distribution::query()
            ->whereMonth('delivery_date', $date->month)
            ->whereYear('delivery_date', $date->year)
            ->where('site_id', $this->siteId)
            ->where('delivery_type_id', $this->deliveryTypeId);

        if ($this->distributionId) {
            $query->where('id', '!=', $this->distributionId);
        }

        if ($query->exists()) {

            $monthYear = $date->translatedFormat('F Y'); // 'F' pour le nom complet du mois, 'Y' pour l'année sur 4 chiffres
          //  $fail("Une distribution existe déjà pour ce site et ce type de livraison pour le mois de {$monthYear}.");
            $fail("Pour ce mois de {$monthYear}, Une distribution du même type existe déjà pour ce site.");

        }

    }
}
