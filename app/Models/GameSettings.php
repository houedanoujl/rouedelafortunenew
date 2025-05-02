<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSettings extends Model
{
    protected $fillable = [
        'win_probability',
        'name'
    ];

    // Singleton pattern - toujours récupérer la première instance
    public static function instance()
    {
        $settings = static::first();
        if (!$settings) {
            $settings = static::create([
                'name' => 'default',
                'win_probability' => 20 // 20% par défaut
            ]);
        }
        return $settings;
    }
}
