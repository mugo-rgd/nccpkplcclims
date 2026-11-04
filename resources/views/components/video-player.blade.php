@props([
    'id' => 'labVideo',
    'class' => '',
    'autoplay' => true,
    'muted' => true,
    'loop' => true,
    'playsinline' => true,
    'poster' => null,
    'showControls' => false,
    'fallbackText' => '🎵 Meter Central Laboratory Video'
])

@php
    $videoPath = public_path('videos/music-motional-lab.mp4');
    $videoExists = file_exists($videoPath);
    $videoUrl = $videoExists ? asset('videos/music-motional-lab.mp4') : null;

    // Fallback videos
    $fallbackUrls = [
        'https://www.w3schools.com/html/mov_bbb.mp4',
        'https://www.youtube.com/watch?v=oEt9nE7vPFM'
         ];
@endphp

<div {{ $attributes->merge(['class' => 'video-container']) }}>
    @if($videoExists)
        <video
            id="{{ $id }}"
            {{ $autoplay ? 'autoplay' : '' }}
            {{ $muted ? 'muted' : '' }}
            {{ $loop ? 'loop' : '' }}
            {{ $playsinline ? 'playsinline' : '' }}
            {{ $poster ? "poster={$poster}" : '' }}
            preload="auto"
            class="{{ $class }}"
            style="width:100%;height:100%;object-fit:cover;"
        >
            <source src="{{ $videoUrl }}" type="video/mp4">

            @foreach($fallbackUrls as $url)
                <source src="{{ $url }}" type="video/mp4">
            @endforeach

            {{ $fallbackText }}
        </video>
    @else
        <!-- Fallback: Show placeholder with video info -->
        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#02162f,#0057b8);color:#fff;flex-direction:column;padding:40px;text-align:center;min-height:300px;">
            <i class="fas fa-video" style="font-size:4rem;color:var(--kplc-yellow, #ffd200);margin-bottom:20px;"></i>
            <h3 style="color:#fff;margin-bottom:10px;">{{ $fallbackText }}</h3>
            <p style="color:rgba(255,255,255,0.7);font-size:0.9rem;max-width:500px;">
                Experience our state-of-the-art meter calibration/testing laboratory in action.
                <br>
                <small style="opacity:0.5;">Video file: music-motional-lab.mp4</small>
            </p>
            <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;justify-content:center;">
                <span style="padding:6px 16px;border-radius:999px;background:rgba(255,255,255,0.1);font-size:0.8rem;">
                    <i class="fas fa-music"></i> Music
                </span>
                <span style="padding:6px 16px;border-radius:999px;background:rgba(255,255,255,0.1);font-size:0.8rem;">
                    <i class="fas fa-arrows-up-down"></i> Motion
                </span>
                <span style="padding:6px 16px;border-radius:999px;background:rgba(255,255,255,0.1);font-size:0.8rem;">
                    <i class="fas fa-flask-vial"></i> Laboratory
                </span>
            </div>
            <div style="margin-top:20px;padding:12px 20px;border-radius:12px;background:rgba(255,210,0,0.1);border:1px dashed rgba(255,210,0,0.3);">
                <small style="color:rgba(255,255,255,0.5);">
                    <i class="fas fa-info-circle"></i>
                    Place video at: <code style="background:rgba(0,0,0,0.3);padding:2px 8px;border-radius:4px;color:var(--kplc-yellow, #ffd200);">public/videos/music-motional-lab.mp4</code>
                </small>
            </div>
        </div>
    @endif
</div>
