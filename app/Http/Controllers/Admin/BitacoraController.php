<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class BitacoraController extends Controller
{
    public function index()
    {
        $path = base_path('ProyectoIA/DocumentacionProyecto/Bitacora.md');
        $entries = [];

        if (File::exists($path)) {
            $content = File::get($path);
            $blocks = preg_split('/\n---\n/', $content);
            foreach ($blocks as $block) {
                $block = trim($block);
                if ($block === '' || str_starts_with($block, '# ')) {
                    continue;
                }
                if (preg_match('/^##\s+(\d{4}-\d{2}-\d{2})\s+—\s+(.+)$/m', $block, $m)) {
                    $entries[] = [
                        'fecha' => $m[1],
                        'titulo' => trim($m[2]),
                        'contenido' => trim(preg_replace('/^##[^\n]+\n/', '', $block, 1)),
                    ];
                }
            }
            $entries = array_reverse($entries);
        }

        return view('admin.bitacora.index', compact('entries', 'path'));
    }
}
