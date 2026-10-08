{{--
    Tabel standar (satu-satunya cara membuat tabel admin).
    Pakai:
      <x-admin.table>
          <x-slot:head><tr><th>Nama</th><th>Status</th></tr></x-slot:head>
          @foreach ($rows as $row)
              <tr><td>{{ $row->name }}</td><td>...</td></tr>
          @endforeach
      </x-admin.table>
--}}
@props(['head' => null])

<div class="table-wrap">
    <table {{ $attributes->class(['table']) }}>
        @isset($head)
            <thead>{{ $head }}</thead>
        @endisset
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
