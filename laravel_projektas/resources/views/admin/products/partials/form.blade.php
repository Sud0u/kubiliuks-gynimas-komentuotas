@php
    $p = $product;

    function adminProductImageUrl($path) {
        if (!$path) return null;

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }

    $galleryImages = $p?->gallery_images ?? [];

    if (is_string($galleryImages)) {
        $galleryImages = json_decode($galleryImages, true) ?: [];
    }

    $galleryImages = array_values(array_filter($galleryImages ?: []));

    $img1 = ($p && $p->image) ? adminProductImageUrl($p->image) : null;
    $img2 = ($p && $p->image_2) ? adminProductImageUrl($p->image_2) : null;
    $img3 = ($p && $p->image_3) ? adminProductImageUrl($p->image_3) : null;
@endphp

<style>
    .f-label { font-size: 0.875rem; font-weight: 600; color: #1c1917; }
    .f-input, .f-select, .f-textarea {
        width: 100%;
        border: 1px solid rgba(28, 25, 23, 0.15);
        border-radius: 14px;
        padding: 10px 12px;
        background: #fff;
        outline: none;
    }
    .f-input:focus, .f-select:focus, .f-textarea:focus {
        border-color: rgba(180, 83, 9, 0.65);
        box-shadow: 0 0 0 3px rgba(180, 83, 9, 0.15);
    }
    .f-card {
        border: 1px solid rgba(28, 25, 23, 0.10);
        border-radius: 18px;
        background: #fff;
        padding: 16px;
    }
    .f-divider { height: 1px; background: rgba(28, 25, 23, 0.08); margin: 16px 0; }

    .toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 999px;
        border: 1px solid rgba(28, 25, 23, 0.12);
        background: #fff;
        font-size: 0.875rem;
        user-select: none;
    }

    .img-grid{
        display:grid;
        grid-template-columns: 1fr;
        gap: 14px;
    }
    @media (min-width: 900px){
        .img-grid{ grid-template-columns: repeat(3, 1fr); }
    }

    .img-tile{
        border: 1px solid rgba(28, 25, 23, 0.10);
        border-radius: 16px;
        background: #fafaf9;
        overflow: hidden;
    }

    .img-preview{
        width: 100%;
        height: 160px;
        object-fit: cover;
        display:block;
        background: #f5f5f4;
    }

    .img-actions{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        padding: 10px;
        border-top: 1px solid rgba(28, 25, 23, 0.08);
        background: #fff;
    }

    .btn-file{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding: 9px 12px;
        border-radius: 12px;
        border: 1px solid rgba(28, 25, 23, 0.12);
        background: #ffffff;
        font-weight: 700;
        font-size: 0.875rem;
        cursor: pointer;
    }
    .btn-file:hover{
        border-color: rgba(28, 25, 23, 0.18);
        background: #fbfbfa;
    }

    .file-name{
        font-size: 0.825rem;
        color: #78716c;
        overflow:hidden;
        white-space:nowrap;
        text-overflow:ellipsis;
        max-width: 160px;
    }

    .file-input-hidden{
        position:absolute;
        left:-9999px;
        width:1px;
        height:1px;
        opacity:0;
    }

    .gallery-preview-grid{
        display:grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    @media (min-width: 900px){
        .gallery-preview-grid{ grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }

    .gallery-card{
        border: 1px solid rgba(28, 25, 23, 0.10);
        border-radius: 14px;
        overflow:hidden;
        background:#fff;
    }

    .gallery-card img{
        width:100%;
        height:110px;
        object-fit:cover;
        display:block;
        background:#f5f5f4;
    }

    .gallery-remove{
        display:flex;
        align-items:center;
        gap:8px;
        padding:9px 10px;
        font-size:0.8rem;
        font-weight:700;
        color:#7f1d1d;
        background:#fff;
        border-top:1px solid rgba(28, 25, 23, 0.08);
    }

    .err{ margin-top:6px; color:#dc2626; font-size: 0.8rem; font-weight:600; }
</style>

<div class="f-card">
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
            <div class="f-label">Kategorija</div>
            <select name="category_id" class="f-select" style="margin-top:6px;">
                <option value="">-- pasirinkti --</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id', $p?->category_id) == $c->id)>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex; align-items:flex-end; justify-content:flex-end;">
            <label class="toggle">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $p?->is_active ?? true))>
                Aktyvi
            </label>
        </div>

        <div style="grid-column: 1 / -1;">
            <div class="f-label">Pavadinimas</div>
            <input name="name" value="{{ old('name', $p?->name) }}" class="f-input" style="margin-top:6px;" />
            @error('name') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div>
            <div class="f-label">Kaina (€)</div>
            <input type="number" step="0.01" name="price" value="{{ old('price', $p?->price) }}"
                   class="f-input" style="margin-top:6px;" />
            @error('price') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div>
            <div class="f-label">Likutis</div>
            <input type="number" min="0" name="stock" value="{{ old('stock', $p?->stock ?? 0) }}"
                   class="f-input" style="margin-top:6px;" />
            @error('stock') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div style="grid-column: 1 / -1;">
            <div class="f-label">Pagaminimo terminas</div>
            <input
                name="production_time"
                value="{{ old('production_time', $p?->production_time) }}"
                class="f-input"
                style="margin-top:6px;"
            />
            @error('production_time') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div style="grid-column: 1 / -1;">
            <div class="f-label">Aprašymas</div>
            <textarea name="description" rows="6" class="f-textarea" style="margin-top:6px;">{{ old('description', $p?->description) }}</textarea>
            @error('description') <div class="err">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="f-divider"></div>

    <div>
        <div class="f-label">Pagrindinės nuotraukos</div>

        <div class="img-grid" style="margin-top:10px;">

            <div class="img-tile">
                <img id="prev1" class="img-preview" src="{{ $img1 ?: asset('images/no-image.png') }}" alt="">
                <div class="img-actions">
                    <label class="btn-file" for="img1">Pasirinkti</label>
                    <div id="name1" class="file-name">Nepasirinkta</div>
                </div>
                <input id="img1" type="file" name="image" class="file-input-hidden" accept="image/*">
                @error('image') <div class="err" style="padding:0 10px 10px 10px;">{{ $message }}</div> @enderror
            </div>

            <div class="img-tile">
                <img id="prev2" class="img-preview" src="{{ $img2 ?: asset('images/no-image.png') }}" alt="">
                <div class="img-actions">
                    <label class="btn-file" for="img2">Pasirinkti</label>
                    <div id="name2" class="file-name">Nepasirinkta</div>
                </div>
                <input id="img2" type="file" name="image_2" class="file-input-hidden" accept="image/*">
                @error('image_2') <div class="err" style="padding:0 10px 10px 10px;">{{ $message }}</div> @enderror
            </div>

            <div class="img-tile">
                <img id="prev3" class="img-preview" src="{{ $img3 ?: asset('images/no-image.png') }}" alt="">
                <div class="img-actions">
                    <label class="btn-file" for="img3">Pasirinkti</label>
                    <div id="name3" class="file-name">Nepasirinkta</div>
                </div>
                <input id="img3" type="file" name="image_3" class="file-input-hidden" accept="image/*">
                @error('image_3') <div class="err" style="padding:0 10px 10px 10px;">{{ $message }}</div> @enderror
            </div>

        </div>
    </div>

    <div class="f-divider"></div>

    <div>
        <div class="f-label">Papildomos nuotraukos</div>

        @if(count($galleryImages))
            <div class="gallery-preview-grid" style="margin-top:10px;">
                @foreach($galleryImages as $galleryImage)
                    <div class="gallery-card">
                        <img src="{{ adminProductImageUrl($galleryImage) }}" alt="">
                        <label class="gallery-remove">
                            <input type="checkbox" name="remove_gallery_images[]" value="{{ $galleryImage }}">
                            Pašalinti
                        </label>
                    </div>
                @endforeach
            </div>
        @endif

        <div style="margin-top:12px;">
            <label class="btn-file" for="galleryImages">Pridėti nuotraukų</label>
            <input id="galleryImages" type="file" name="gallery_images[]" class="file-input-hidden" accept="image/*" multiple>
            <div id="galleryNames" class="file-name" style="max-width:100%; margin-top:8px;">Nepasirinkta</div>
            <div id="galleryPreview" class="gallery-preview-grid" style="margin-top:10px;"></div>
            @error('gallery_images') <div class="err">{{ $message }}</div> @enderror
            @error('gallery_images.*') <div class="err">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<script>
(function(){
    function wire(inputId, previewId, nameId){
        const input = document.getElementById(inputId);
        const prev  = document.getElementById(previewId);
        const name  = document.getElementById(nameId);
        if(!input || !prev || !name) return;

        input.addEventListener('change', () => {
            const file = input.files && input.files[0] ? input.files[0] : null;
            name.textContent = file ? file.name : 'Nepasirinkta';

            if(!file) return;
            const url = URL.createObjectURL(file);
            prev.src = url;
        });
    }

    const galleryInput = document.getElementById('galleryImages');
    const galleryNames = document.getElementById('galleryNames');
    const galleryPreview = document.getElementById('galleryPreview');

    if (galleryInput && galleryNames && galleryPreview) {
        galleryInput.addEventListener('change', function () {
            const files = Array.from(galleryInput.files || []);

            galleryPreview.innerHTML = '';
            galleryNames.textContent = files.length ? `${files.length} nuotr.` : 'Nepasirinkta';

            files.forEach((file) => {
                const url = URL.createObjectURL(file);
                const item = document.createElement('div');
                item.className = 'gallery-card';
                item.innerHTML = `<img src="${url}" alt=""><div class="gallery-remove">${file.name}</div>`;
                galleryPreview.appendChild(item);
            });
        });
    }

    wire('img1','prev1','name1');
    wire('img2','prev2','name2');
    wire('img3','prev3','name3');
})();
</script>