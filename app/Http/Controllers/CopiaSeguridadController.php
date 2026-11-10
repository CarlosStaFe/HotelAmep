<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CopiaSeguridadController extends Controller
{
    private const PATRON_ARCHIVO = '/^[A-Za-z0-9_\-]+\.sql$/';

    private function directorio(): string
    {
        $ruta = storage_path('app/backups');
        File::ensureDirectoryExists($ruta);

        return $ruta;
    }

    private function listarCopias(): array
    {
        return collect(File::files($this->directorio()))
            ->filter(fn ($archivo) => preg_match(self::PATRON_ARCHIVO, $archivo->getFilename()))
            ->sortByDesc(fn ($archivo) => $archivo->getMTime())
            ->map(fn ($archivo) => [
                'nombre' => $archivo->getFilename(),
                'tamano' => round($archivo->getSize() / 1024, 1),
                'fecha' => date('d/m/Y H:i:s', $archivo->getMTime()),
            ])
            ->values()
            ->all();
    }

    private function rutaCopia(string $nombre): string
    {
        abort_unless(preg_match(self::PATRON_ARCHIVO, $nombre), 404);
        $ruta = $this->directorio() . DIRECTORY_SEPARATOR . $nombre;
        abort_unless(File::exists($ruta), 404);

        return $ruta;
    }

    public function index()
    {
        return view('admin.copias.index', ['copias' => $this->listarCopias()]);
    }

    public function restaurarForm()
    {
        return view('admin.copias.restaurar', ['copias' => $this->listarCopias()]);
    }

    public function store()
    {
        $nombre = 'backup_' . date('Ymd_His') . '.sql';
        $ruta = $this->directorio() . DIRECTORY_SEPARATOR . $nombre;
        $pdo = DB::connection()->getPdo();
        $manejador = fopen($ruta, 'w');

        try {
            fwrite($manejador, "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n");

            $tablas = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
            foreach ($tablas as $fila) {
                $tabla = array_values((array) $fila)[0];
                $crear = (array) DB::selectOne("SHOW CREATE TABLE `{$tabla}`");
                fwrite($manejador, "DROP TABLE IF EXISTS `{$tabla}`;\n" . array_values($crear)[1] . ";\n\n");

                foreach (DB::table($tabla)->cursor() as $registro) {
                    $valores = array_map(
                        fn ($valor) => $valor === null ? 'NULL' : $pdo->quote((string) $valor),
                        array_values((array) $registro)
                    );
                    fwrite($manejador, "INSERT INTO `{$tabla}` VALUES (" . implode(',', $valores) . ");\n");
                }
                fwrite($manejador, "\n");
            }

            fwrite($manejador, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($manejador);
        } catch (\Throwable $e) {
            fclose($manejador);
            File::delete($ruta);

            return redirect()->route('copias.index')->with('error', 'No se pudo generar la copia: ' . $e->getMessage());
        }

        return redirect()->route('copias.index')->with('success', "Copia de seguridad {$nombre} generada correctamente.");
    }

    public function descargar(string $nombre)
    {
        return response()->download($this->rutaCopia($nombre));
    }

    public function destroy(string $nombre)
    {
        File::delete($this->rutaCopia($nombre));

        return redirect()->route('copias.index')->with('success', 'Copia eliminada correctamente.');
    }

    public function restaurar(Request $request)
    {
        $request->validate([
            'archivo' => 'required_without:copia|nullable|file|max:204800',
            'copia' => 'required_without:archivo|nullable|string',
        ]);

        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            if (strtolower($archivo->getClientOriginalExtension()) !== 'sql') {
                return back()->with('error', 'El archivo debe tener extensión .sql.');
            }
            $ruta = $archivo->getRealPath();
        } else {
            $ruta = $this->rutaCopia($request->input('copia'));
        }

        try {
            $sql = File::get($ruta);
            DB::connection()->getPdo()->exec($sql);
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo restaurar la copia: ' . $e->getMessage());
        }

        return redirect()->route('copias.restaurar')->with('success', 'Copia de seguridad restaurada correctamente.');
    }
}
