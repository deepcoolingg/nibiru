@php
    use Filament\Widgets\View\Components\ChartWidgetComponent;
    use Illuminate\View\ComponentAttributeBag;

    $color = $this->getColor();
    $heading = $this->getHeading();
    $filters = $this->getFilters();
    $isCollapsible = $this->isCollapsible();
    $type = $this->getType();
    $targetData = $this->getTargetData();
@endphp

<x-filament-widgets::widget class="fi-wi-chart">
    <x-filament::section
        :heading="$heading"
        :collapsible="$isCollapsible"
    >
        <div
            @if ($pollingInterval = $this->getPollingInterval())
                wire:poll.{{ $pollingInterval }}="updateChartData"
            @endif
        >
            {{-- Chart --}}
            <div
                x-load
                x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
                wire:ignore
                data-chart-type="{{ $type }}"
                x-data="chart({
                    cachedData: @js($this->getCachedData()),
                    maxHeight: @js($maxHeight = $this->getMaxHeight()),
                    options: @js($this->getOptions()),
                    type: @js($type),
                })"
                {{
                    (new ComponentAttributeBag)
                        ->color(ChartWidgetComponent::class, $color)
                        ->class([
                            'fi-wi-chart-canvas-ctn',
                            'fi-wi-chart-canvas-ctn-no-aspect-ratio' => filled($maxHeight),
                        ])
                }}
            >
                <canvas
                    x-ref="canvas"
                    @if ($maxHeight)
                        style="max-height: {{ $maxHeight }}"
                    @endif
                ></canvas>

                <span x-ref="backgroundColorElement" class="fi-wi-chart-bg-color"></span>
                <span x-ref="borderColorElement" class="fi-wi-chart-border-color"></span>
                <span x-ref="gridColorElement" class="fi-wi-chart-grid-color"></span>
                <span x-ref="textColorElement" class="fi-wi-chart-text-color"></span>
            </div>

            {{-- Percentage --}}
            <div style="text-align: center; margin-top: -2.5rem;">
                <span style="font-size: 2.25rem; font-weight: 700; line-height: 1; color: #bcd5fb;">
                    {{ $targetData['persen'] }}%
                </span>
            </div>

            {{-- Narasi below --}}
            <div class="flex flex-col items-center justify-center mt-2">
                <span class="text-sm text-gray-500 dark:text-gray-400 text-center">
                    {!! $targetData['narasi'] !!}
                </span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
