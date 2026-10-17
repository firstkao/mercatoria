@php($announcement = \App\Models\Announcement::query()->activeNow()->first())
@if ($announcement)
    <aside id="announcement-bar" class="relative bg-navy-500 px-10 py-2 text-center text-sm text-white"
        data-announcement-id="{{ $announcement->id }}">
        @if ($announcement->link_url)
            <a href="{{ $announcement->link_url }}" class="font-medium underline underline-offset-2">{{ $announcement->text }}</a>
        @else
            <span>{{ $announcement->text }}</span>
        @endif
        <button type="button" id="dismiss-announcement" aria-label="Tutup pengumuman"
            class="absolute right-3 top-1/2 -translate-y-1/2 rounded px-2 py-1 hover:bg-white/20">×</button>
    </aside>
    @once
        @push('scripts')
            <script>
                (() => {
                    const bar = document.getElementById('announcement-bar');
                    const button = document.getElementById('dismiss-announcement');
                    if (!bar || !button) return;
                    const key = `mercatoria-announcement-${bar.dataset.announcementId}`;
                    let dismissedAt = 0;
                    try {
                        dismissedAt = Number(localStorage.getItem(key) || 0);
                    } catch (error) {
                        console.error('Announcement dismissal preference could not be loaded.', error);
                    }
                    if (Date.now() - dismissedAt < 7 * 24 * 60 * 60 * 1000) bar.hidden = true;
                    button.addEventListener('click', () => {
                        bar.hidden = true;
                        try {
                            localStorage.setItem(key, String(Date.now()));
                        } catch (error) {
                            console.error('Announcement dismissal preference could not be saved.', error);
                        }
                    });
                })();
            </script>
        @endpush
    @endonce
@endif
