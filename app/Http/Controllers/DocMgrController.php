<?php

namespace App\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use stdClass;

class DocMgrController extends Controller
{
  public static function save($val, $doc, $dlt, $fld)
  {
    if (!GenController::empty($doc)) {
      $name = Str::random(43) . '.' . $doc->getClientOriginalExtension();
      Storage::disk($fld)->put($name, file_get_contents($doc));

      if (!GenController::empty($val)) {
        Storage::disk($fld)->delete($val);
      }

      return $name;
    } else {
      if (GenController::filter($dlt, 'b')) {
        Storage::disk($fld)->delete($val);

        return null;
      }
    }

    return GenController::empty($val) ? null : $val;
  }

  public static function replaceOrDelete(?string $current_path, ?UploadedFile $doc, string $fld): ?string
  {
    $disk = Storage::disk($fld);
    $has_current = !is_null($current_path);

    if ($doc) {
      $ext = strtolower($doc->getClientOriginalExtension() ?: $doc->extension() ?: 'bin');
      $name = Str::random(40) . '.' . $ext;

      $disk->putFileAs('', $doc, $name);

      if ($has_current) {
        $disk->delete($current_path);
      }

      return $name;
    }

    if ($has_current) {
      $disk->delete($current_path);
    }

    return null;
  }

  public static function getB64($val, $fld)
  {
    if (!empty($val)) {
      $path = Storage::disk($fld)->path($val);

      if (file_exists($path)) {
        $b64 = new stdClass;
        $b64->cnt = base64_encode(file_get_contents($path));
        $b64->ext = pathinfo($path, PATHINFO_EXTENSION);
        $b64->mime = Storage::disk($fld)->mimeType($val);
        $b64->path = $path;
        $b64->name = $val;

        return $b64;
      }
    }

    return null;
  }

  public static function exist($path)
  {
    return is_file($path) ? $path : null;
  }
}
