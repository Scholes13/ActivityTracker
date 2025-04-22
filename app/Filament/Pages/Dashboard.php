<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LeaderSubTasksOverview;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\SubTaskActivitiesOverview;
use App\Filament\Widgets\SubTaskProgressChart;
use App\Filament\Widgets\DeadlineOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * @return array<class-string<\Filament\Widgets\Widget>>
     */
    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            SubTaskProgressChart::class,
            LeaderSubTasksOverview::class,
            SubTaskActivitiesOverview::class,
            DeadlineOverview::class,
        ];
    }
    
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'lg' => 3,
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            // Untuk tambahan widget di footer dashboard
        ];
    }
} 