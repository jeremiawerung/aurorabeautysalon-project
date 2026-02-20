<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Slot_jadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlotJadwalController extends Controller
{
    public function index()
    {
        return view('admin.slot_jadwal.index');
    }

    public function ajax()
    {
        try {
            $slotJadwal = Slot_jadwal::with('layanan')
                ->orderBy('id_layanan')
                ->orderBy('waktu')
                ->get()
                ->groupBy('id_layanan')
                ->map(function ($group) {
                    /** @var \Illuminate\Support\Collection $group */
                    $sorted = $group->sortBy('waktu');
                    $first = $sorted->first();
                    $last = $sorted->last();

                    $layanan = $first->layanan;
                    $durasi = $layanan->durasi ?? 0;

                    $start = Carbon::parse($first->waktu)->format('H:i');
                    $end = Carbon::parse($last->waktu);
                    if ($durasi > 0) {
                        $end = $end->addMinutes($durasi);
                    }
                    $endStr = $end->format('H:i');

                    $status = $group->contains(function ($slot) {
                        return $slot->status_slot === 'aktif';
                    }) ? 'aktif' : 'non-aktif';

                    return [
                        'id_slot' => $first->id_slot,
                        'id_layanan' => $first->id_layanan,
                        'layanan' => [
                            'id_layanan' => $layanan->id_layanan,
                            'nama_layanan' => $layanan->nama_layanan,
                            'durasi' => $layanan->durasi,
                        ],
                        'waktu' => $start.' - '.$endStr,
                        'status_slot' => $status,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $slotJadwal,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching slot jadwal AJAX: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data slot jadwal.',
            ], 500);
        }
    }

    public function create()
    {
        $layanan = Layanan::all();

        $slotByLayanan = Slot_jadwal::orderBy('waktu')->get()->groupBy('id_layanan');

        return view('admin.slot_jadwal.create', compact('layanan', 'slotByLayanan'));
    }

    protected function validateSlotsWithDuration(array $waktuList, int $durasiMenit)
    {
        $waktuList = array_filter($waktuList, fn ($w) => $w !== null && $w !== '');
        if (count($waktuList) === 0) {
            return;
        }

        $countPerTime = array_count_values($waktuList);
        foreach ($countPerTime as $time => $count) {
            if ($count > 1) {
                throw new \Exception(
                    'Tidak boleh ada slot dengan waktu yang sama: '.$time
                );
            }
        }

        if (count($waktuList) <= 1 || $durasiMenit <= 0) {
            return;
        }

        $unique = array_values(array_unique($waktuList));
        sort($unique);

        $prevTime = null;
        foreach ($unique as $w) {
            $cur = Carbon::createFromFormat('H:i', $w);
            if ($prevTime) {
                $diff = $prevTime->diffInMinutes($cur);
                if ($diff < $durasiMenit) {
                    throw new \Exception(
                        'Slot waktu bertabrakan. Minimal jarak antar slot adalah '.$durasiMenit.' menit.'
                    );
                }
            }
            $prevTime = $cur;
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_layanan' => 'required|exists:layanan,id_layanan',
            'waktu' => 'required|array|min:1',
            'waktu.*' => 'required|date_format:H:i',
            'status_slot' => 'required|array|min:1',
            'status_slot.*' => 'required|string|in:aktif,non-aktif',
        ]);

        try {
            $layanan = Layanan::findOrFail($request->id_layanan);
            $durasi = (int) ($layanan->durasi ?? 0);

            $waktuList = $request->waktu;
            $statusList = $request->status_slot;

            if (count($waktuList) !== count($statusList)) {
                throw new \Exception('Jumlah waktu dan status slot tidak sama.');
            }

            $this->validateSlotsWithDuration($waktuList, $durasi);

            DB::transaction(function () use ($request, $waktuList, $statusList) {
                Slot_jadwal::where('id_layanan', $request->id_layanan)->delete();

                foreach ($waktuList as $index => $w) {
                    Slot_jadwal::create([
                        'id_layanan' => $request->id_layanan,
                        'waktu' => $w,
                        'status_slot' => $statusList[$index] ?? 'aktif',
                        'is_default' => $request->is_default ?? 1,
                    ]);
                }
            });

            session()->flash('success', '✅ Slot jadwal berhasil disimpan!');

            return redirect()->route('slot-jadwal.index');
        } catch (\Exception $e) {
            Log::error('Error menyimpan slot jadwal: '.$e->getMessage());
            session()->flash('error', '❌ Gagal menyimpan slot jadwal: '.$e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        $slotUtama = Slot_jadwal::with('layanan')->findOrFail($id);
        $layanan = Layanan::all();

        $slots = Slot_jadwal::where('id_layanan', $slotUtama->id_layanan)
            ->orderBy('waktu')
            ->get();

        return view('admin.slot_jadwal.edit', [
            'slotJadwal' => $slotUtama,
            'layanan' => $layanan,
            'slots' => $slots,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_layanan' => 'required|exists:layanan,id_layanan',
            'waktu' => 'required|array|min:1',
            'waktu.*' => 'required|date_format:H:i',
            'status_slot' => 'required|array|min:1',
            'status_slot.*' => 'required|string|in:aktif,non-aktif',
        ]);

        try {
            $slotUtama = Slot_jadwal::findOrFail($id);
            $layanan = Layanan::findOrFail($request->id_layanan);
            $durasi = (int) ($layanan->durasi ?? 0);

            $waktuList = $request->waktu;
            $statusList = $request->status_slot;

            if (count($waktuList) !== count($statusList)) {
                throw new \Exception('Jumlah waktu dan status slot tidak sama.');
            }

            $this->validateSlotsWithDuration($waktuList, $durasi);

            DB::transaction(function () use ($slotUtama, $request, $waktuList, $statusList) {
                // Hapus semua slot lama untuk layanan ini
                Slot_jadwal::where('id_layanan', $slotUtama->id_layanan)->delete();

                // Buat ulang slot berdasarkan input baru
                foreach ($waktuList as $index => $w) {
                    Slot_jadwal::create([
                        'id_layanan' => $request->id_layanan,
                        'waktu' => $w,
                        'status_slot' => $statusList[$index] ?? 'aktif',
                        'is_default' => $request->is_default ?? 1,
                    ]);
                }
            });

            session()->flash('success', 'Slot jadwal berhasil diperbarui!');

            return redirect()->route('slot-jadwal.index');
        } catch (\Exception $e) {
            Log::error('Error mengupdate slot jadwal: '.$e->getMessage());
            session()->flash('error', 'Gagal memperbarui slot jadwal: '.$e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $slot = Slot_jadwal::findOrFail($id);
            $idLayanan = $slot->id_layanan;

            DB::transaction(function () use ($idLayanan) {
                Slot_jadwal::where('id_layanan', $idLayanan)->delete();
            });

            session()->flash('success', '✅ Semua slot jadwal untuk layanan tersebut berhasil dihapus!');

            return redirect()->route('slot-jadwal.index');
        } catch (\Exception $e) {
            Log::error('Error menghapus slot jadwal: '.$e->getMessage());
            session()->flash('error', '❌ Terjadi kesalahan saat menghapus slot jadwal.');

            return redirect()->route('slot-jadwal.index');
        }
    }
}
