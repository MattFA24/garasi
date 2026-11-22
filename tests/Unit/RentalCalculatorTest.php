<?php

namespace Tests\Unit;

use App\Models\Garage;
use App\Services\RentalCalculator;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RentalCalculatorTest extends TestCase
{
    private RentalCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new RentalCalculator;
    }

    /** T01: November punya 30 hari. */
    public function test_panjang_bulan_november(): void
    {
        $this->assertSame(30, $this->calculator->monthLength(Carbon::parse('2026-11-21')));
    }

    /** T02: Februari tahun kabisat punya 29 hari. */
    public function test_februari_kabisat(): void
    {
        $this->assertSame(29, $this->calculator->monthLength(Carbon::parse('2028-02-10')));
    }

    /** T03: mulai di Desember, sewa harian maksimal 30 hari. */
    public function test_maksimal_harian_desember(): void
    {
        $this->assertSame(30, $this->calculator->maxDailyDays(Carbon::parse('2026-12-15')));
    }

    /** T04: mulai November, 29 hari boleh harian, 30 hari tidak boleh. */
    public function test_batas_harian_november(): void
    {
        $max = $this->calculator->maxDailyDays(Carbon::parse('2026-11-21'));

        $this->assertTrue($max >= 29);
        $this->assertFalse($max >= 30);
    }

    /** T05: mulai 21-11-2026 selama 3 hari, selesai 23-11-2026. */
    public function test_tanggal_selesai_harian(): void
    {
        $end = $this->calculator->endDate(Carbon::parse('2026-11-21'), 3);

        $this->assertSame('2026-11-23', $end->toDateString());
    }

    /** T06: bulanan mulai 21-11-2026, selesai 20-12-2026. */
    public function test_tanggal_selesai_bulanan(): void
    {
        $start = Carbon::parse('2026-11-21');
        $end = $this->calculator->endDate($start, $this->calculator->monthLength($start));

        $this->assertSame('2026-12-20', $end->toDateString());
    }

    /** T07: sepeda 3 hari x 5.000 = 15.000. */
    public function test_biaya_harian(): void
    {
        $this->assertSame(15000, $this->calculator->rentCost('daily', 5000, 3));
    }

    /** T08: bulanan = 25 x 20.000 = 500.000. */
    public function test_biaya_bulanan(): void
    {
        $this->assertSame(500000, $this->calculator->rentCost('monthly', 20000, 30));
    }

    /** T09: (60 - 15) kWh x 2.500 = 112.500. */
    public function test_biaya_listrik(): void
    {
        $this->assertSame(112500, $this->calculator->chargingCost(60, 15, 2500));
    }

    /** T10: sisa baterai 70 lebih besar dari kapasitas 60 harus ditolak. */
    public function test_sisa_baterai_lebih_dari_kapasitas_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->chargingCost(60, 70, 2500);
    }

    /** T11: total = biaya sewa + biaya listrik, tanpa tambahan apa pun. */
    public function test_total_mobil_listrik_di_garasi_e(): void
    {
        $total = $this->calculator->rentCost('daily', 30000, 2)
            + $this->calculator->chargingCost(60, 15, 2500);

        $this->assertSame(172500, $total);
    }

    /** T12: perpanjang 23-11 ke 26-11 = 3 hari x 20.000, dan harus positif. */
    public function test_perpanjang_harian(): void
    {
        $cost = $this->calculator->extensionCost(20000, Carbon::parse('2026-11-23'), Carbon::parse('2026-11-26'));

        $this->assertSame(60000, $cost);
        $this->assertGreaterThan(0, $cost);
    }

    /** T13: perpanjang 30-11 ke 31-12 = 31 hari di Desember = 1 bulan = 500.000. */
    public function test_perpanjang_satu_bulan(): void
    {
        $cost = $this->calculator->extensionCost(20000, Carbon::parse('2026-11-30'), Carbon::parse('2026-12-31'));

        $this->assertSame(500000, $cost);
    }

    /** T14: garasi mobil ke-10 berkode C010. */
    public function test_kode_garasi(): void
    {
        $this->assertSame('C010', Garage::makeCode('car', 10));
    }

    /** T15: sisa baterai negatif harus ditolak. */
    public function test_sisa_baterai_negatif_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->chargingCost(60, -5, 2500);
    }

    /** T16: perpanjang ke tanggal yang sama dengan tanggal selesai lama harus ditolak. */
    public function test_perpanjang_ke_tanggal_lama_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->extensionCost(20000, Carbon::parse('2026-11-26'), Carbon::parse('2026-11-26'));
    }
}
