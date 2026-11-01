use App\Models\PreorderPage;
use App\Models\User;

test('admin dapat membuat dan mengubah halaman preorder', function () {
    $admin = User::factory()->admin()->create();
    $content = '<section><h2>Preorder Baru</h2><p>Detail produk.</p></section>';

    $this->actingAs($admin)->post('/office/preorder', [
        'title' => 'Preorder Baru',
        'slug' => 'preorder-baru',
        'content' => $content,
        'meta_description' => 'Informasi preorder terbaru.',
        'status' => 'draft',
    ])->assertRedirect(route('office.preorder.index'));

    $page = PreorderPage::where('slug', 'preorder-baru')->firstOrFail();

    expect($page->content)->toBe($content)
        ->and($page->status)->toBe('draft');

    $this->actingAs($admin)->put("/office/preorder/{$page->id}", [
        'title' => 'Preorder Baru',
        'slug' => 'preorder-baru',
        'content' => $content,
        'meta_description' => 'Informasi preorder terbaru.',
        'status' => 'publish',
    ])->assertRedirect(route('office.preorder.index'));

    expect($page->fresh()->status)->toBe('publish');
});

test('halaman preorder publish tampil secara publik dengan html dan meta seo', function () {
    PreorderPage::create([
        'title' => 'Preorder Baru',
        'slug' => 'preorder-baru',
        'content' => '<section><h2>Preorder</h2><p><strong>Detail produk.</strong></p></section>',
        'meta_description' => 'Informasi preorder terbaru.',
        'status' => 'publish',
    ]);

    $this->get('/preorder-baru')
        ->assertOk()
        ->assertSee('<strong>Detail produk.</strong>', false)
        ->assertSee('name="description" content="Informasi preorder terbaru."', false);
});

test('halaman preorder draft tidak dapat diakses publik', function () {
    PreorderPage::create([
        'title' => 'Preorder Draft',
        'slug' => 'preorder-draft',
        'content' => '<p>Belum dipublish.</p>',
        'status' => 'draft',
    ]);

    $this->get('/preorder-draft')->assertNotFound();
});

test('guest tidak dapat mengelola halaman preorder', function () {
    $this->get('/office/preorder')->assertRedirect('/login');
});
