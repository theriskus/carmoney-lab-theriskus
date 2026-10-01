<?php

declare(strict_types=1);

namespace CarMoneyLab\Domain;

/**
 * Решение по заявке на основании LTV и пробега.
 *
 *   LTV < approve_max                                          -> approve
 *   LTV < approve_max, mileage > review_above_mileage_km       -> review
 *   approve_max <= LTV <= review_max                           -> review
 *   LTV > review_max                                           -> reject
 *
 * Правило пробега только понижает approve до review и не влияет на
 * review/reject по LTV.
 */
final class DecisionEngine
{
    public const APPROVE = 'approve';
    public const REVIEW = 'review';
    public const REJECT = 'reject';

    private float $approveMax;
    private float $reviewMax;

    /** @param array{approve_max:float,review_max:float} $thresholds */
    public function __construct(
        array $thresholds,
        private readonly int $reviewAboveMileageKm = 400000,
    ) {
        $this->approveMax = $thresholds['approve_max'];
        $this->reviewMax = $thresholds['review_max'];
    }

    public function decide(float $ltv, int $mileage = 0): string
    {
        if ($ltv < $this->approveMax) {
            return $mileage > $this->reviewAboveMileageKm ? self::REVIEW : self::APPROVE;
        }

        if ($ltv <= $this->reviewMax) {
            return self::REVIEW;
        }

        return self::REJECT;
    }
}
