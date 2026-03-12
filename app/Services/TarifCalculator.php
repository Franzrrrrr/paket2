<?php

namespace App\Services;

use App\Models\Tarif;
use Carbon\Carbon;

class TarifCalculator
{
    /**
     * Calculate parking fee with accumulation
     */
    public static function calculateFee(string $vehicleType, Carbon $entryTime, Carbon $exitTime, int $parkingAreaId): array
    {
        \Log::info('Calculating fee', ['vehicleType' => $vehicleType, 'entryTime' => $entryTime, 'exitTime' => $exitTime, 'parkingAreaId' => $parkingAreaId]);
        $tarif = self::getTarifForArea($vehicleType, $parkingAreaId);
        \Log::info('Tarif found', ['tarif' => $tarif]);

        if (!$tarif) {
            throw new \Exception("Tarif untuk tipe kendaraan {$vehicleType} di area parkir ini tidak ditemukan");
        }

        $durationInMinutes = $entryTime->diffInMinutes($exitTime);
        $durationInHours = $entryTime->diffInHours($exitTime);

        // Base calculation
        $baseFee = 0;
        $accumulatedFee = 0;
        $totalFee = 0;

        // Calculate base fee (per minute for first hour, then per hour)
        if ($durationInMinutes <= 60) {
            // First hour: use per minute rate
            $baseFee = $tarif->tarif_per_menit * $durationInMinutes;
        } else {
            // First hour
            $baseFee = $tarif->tarif_per_menit * 60;

            // Additional hours
            $additionalHours = $durationInHours - 1;
            $baseFee += $tarif->tarif_per_jam * $additionalHours;

            // Handle remaining minutes after full hours
            $remainingMinutes = $durationInMinutes % 60;
            if ($remainingMinutes > 0) {
                $baseFee += $tarif->tarif_per_menit * $remainingMinutes;
            }
        }

        // Calculate accumulation if duration exceeds thresholds
        $accumulatedFee = self::calculateAccumulation($tarif, $durationInMinutes, $durationInHours);

        $totalFee = $baseFee + $accumulatedFee;

        // Check for overnight parking (denda)
        $overnightFee = self::calculateOvernightFee($tarif, $entryTime, $exitTime);
        $totalFee += $overnightFee;

        return [
            'duration_minutes' => $durationInMinutes,
            'duration_hours' => $durationInHours,
            'base_fee' => $baseFee,
            'accumulated_fee' => $accumulatedFee,
            'overnight_fee' => $overnightFee,
            'total_fee' => $totalFee,
            'tarif_per_menit' => $tarif->tarif_per_menit,
            'tarif_per_jam' => $tarif->tarif_per_jam,
            'breakdown' => [
                'first_hour' => $tarif->tarif_per_menit * 60,
                'additional_hours' => $durationInHours > 1 ? $tarif->tarif_per_jam * ($durationInHours - 1) : 0,
                'remaining_minutes' => ($durationInMinutes % 60) * $tarif->tarif_per_menit,
            ]
        ];
    }

    /**
     * Calculate accumulation fee based on duration thresholds
     */
    private static function calculateAccumulation(Tarif $tarif, int $durationInMinutes, int $durationInHours): float
    {
        $accumulatedFee = 0;

        // Accumulation after X minutes
        if ($tarif->tarif_akumulasi_menit && $durationInMinutes > $tarif->tarif_akumulasi_menit) {
            $excessMinutes = $durationInMinutes - $tarif->tarif_akumulasi_menit;
            $accumulatedFee += $excessMinutes * $tarif->tarif_per_menit * 1.5; // 50% extra after threshold
        }

        // Accumulation after X hours
        if ($tarif->tarif_akumulasi_jam && $durationInHours > $tarif->tarif_akumulasi_jam) {
            $excessHours = $durationInHours - $tarif->tarif_akumulasi_jam;
            $accumulatedFee += $excessHours * $tarif->tarif_per_jam * 2; // 100% extra after threshold
        }

        return $accumulatedFee;
    }

    /**
     * Calculate overnight fee
     */
    private static function calculateOvernightFee(Tarif $tarif, Carbon $entryTime, Carbon $exitTime): float
    {
        $overnightFee = 0;

        // Check if parking spans multiple days
        if ($entryTime->day != $exitTime->day || $entryTime->month != $exitTime->month) {
            $days = $entryTime->diffInDays($exitTime);
            $overnightFee = $days * $tarif->denda_inap_per_hari;
        }

        return $overnightFee;
    }

    /**
     * Get tariff for specific area and vehicle type
     */
    private static function getTarifForArea(string $vehicleType, int $parkingAreaId): ?Tarif
    {
        return Tarif::where('jenis_kendaraan', $vehicleType)
            ->whereHas('areaParkirs', function($query) use ($parkingAreaId) {
                $query->where('area_parkirs.id', $parkingAreaId);
            })
            ->first();
    }

    /**
     * Get current rates for vehicle type
     */
    public static function getCurrentRates(string $vehicleType): ?Tarif
    {
        return Tarif::where('jenis_kendaraan', $vehicleType)->first();
    }

    /**
     * Get rates for specific parking area
     */
    public static function getAreaRates(int $parkingAreaId): array
    {
        $tarifs = Tarif::whereHas('areaParkirs', function($query) use ($parkingAreaId) {
                $query->where('area_parkirs.id', $parkingAreaId);
            })
            ->get()
            ->keyBy('jenis_kendaraan');

        return [
            'mobil' => $tarifs->get('Mobil'),
            'motor' => $tarifs->get('Motor'),
        ];
    }
}
