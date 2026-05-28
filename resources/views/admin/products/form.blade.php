<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0 fw-bold">{{ $product ? 'Editar producto' : 'Nuevo producto' }}</h5>
            <p class="text-muted mb-0" style="font-size:.8rem;">Catálogo · costos · stock · imágenes</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small mb-3">
                <i class="bi bi-exclamation-triangle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="row g-3">

            {{-- ── Columna izquierda: campos ───────────────────────── --}}
            <div class="col-lg-7">

                {{-- Información general --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom py-2 px-3">
                        <h6 class="fw-semibold mb-0 small text-dark">
                            <i class="bi bi-info-circle me-1 text-primary"></i> Información general
                        </h6>
                    </div>
                    <div class="card-body px-3 py-3">
                        <div class="row g-2">
                            @if($companies->count() > 1)
                                <div class="col-12">
                                    <label class="form-label small mb-1">Empresa</label>
                                    <select name="company_id" class="form-select form-select-sm">
                                        @foreach($companies as $c)
                                            <option value="{{ $c->id }}" {{ old('company_id', $product?->company_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-md-8">
                                <label class="form-label small mb-1">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control form-control-sm @error('name') is-invalid @enderror"
                                       value="{{ old('name', $product?->name) }}" required
                                       placeholder="Nombre del producto">
                                @error('name')<div class="invalid-feedback" style="font-size:.72rem;">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small mb-1">SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku"
                                       class="form-control form-control-sm @error('sku') is-invalid @enderror"
                                       value="{{ old('sku', $product?->sku) }}" required
                                       placeholder="Código">
                                @error('sku')<div class="invalid-feedback" style="font-size:.72rem;">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small mb-1">Unidad de medida <span class="text-danger">*</span></label>
                                <div class="d-flex gap-1">
                                    <select name="measurement_unit_id"
                                            class="form-select form-select-sm js-measurement-unit-select @error('measurement_unit_id') is-invalid @enderror"
                                            required>
                                        <option value="">Seleccionar unidad...</option>
                                        @foreach($measurementUnits as $mu)
                                            <option value="{{ $mu->id }}"
                                                {{ old('measurement_unit_id', $product?->measurement_unit_id ?? 1) == $mu->id ? 'selected' : '' }}>
                                                {{ $mu->name }} ({{ $mu->symbol }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('measurement-units.index') }}"
                                       class="btn btn-sm btn-outline-secondary flex-shrink-0"
                                       title="Gestionar unidades" target="_blank">
                                        <i class="bi bi-gear"></i>
                                    </a>
                                </div>
                                @error('measurement_unit_id')<div class="text-danger" style="font-size:.72rem;">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small mb-1">Categoría</label>
                                <select name="category" class="form-select form-select-sm @error('category') is-invalid @enderror">
                                    <option value="">— Seleccionar —</option>
                                    @foreach(['MATERIA PRIMA', 'PRODUCTO FINAL'] as $cat)
                                        <option value="{{ $cat }}"
                                            {{ old('category', $product?->category ?? ($defaultCategory ?? '')) === $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small mb-1">Descripción</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2"
                                          placeholder="Descripción opcional del producto...">{{ old('description', $product?->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Precios & Stock --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom py-2 px-3">
                        <h6 class="fw-semibold mb-0 small text-dark">
                            <i class="bi bi-currency-dollar me-1 text-warning"></i> Precios & Stock
                        </h6>
                    </div>
                    <div class="card-body px-3 py-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Costo <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" name="cost" id="inputCost"
                                           class="form-control form-control-sm @error('cost') is-invalid @enderror"
                                           value="{{ old('cost', $product?->cost) }}" required placeholder="0.00">
                                </div>
                                @error('cost')<div class="text-danger" style="font-size:.72rem;">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small mb-1">Precio de venta <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" name="price" id="inputPrice"
                                           class="form-control form-control-sm @error('price') is-invalid @enderror"
                                           value="{{ old('price', $product?->price) }}" required placeholder="0.00">
                                </div>
                                @error('price')<div class="text-danger" style="font-size:.72rem;">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small mb-1 d-block">&nbsp;</label>
                                <div class="rounded-2 border text-center py-1 px-2" style="background:#f8f9fa;">
                                    <div class="text-muted" style="font-size:.68rem;">MARGEN</div>
                                    <div class="fw-bold small" id="marginDisplay" style="line-height:1.2;">—</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small mb-1">Stock actual</label>
                                <input type="number" step="0.01" min="0" name="current_stock"
                                       class="form-control form-control-sm"
                                       value="{{ old('current_stock', $product?->current_stock ?? 0) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small mb-1">Stock mínimo</label>
                                <input type="number" step="0.01" min="0" name="min_stock"
                                       class="form-control form-control-sm"
                                       value="{{ old('min_stock', $product?->min_stock ?? 0) }}">
                            </div>

                            <div class="col-md-4 d-flex align-items-end pb-1">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="active" value="1"
                                           id="activeSwitch"
                                           {{ old('active', $product?->active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="activeSwitch">Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Guardar --}}
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary px-4" type="submit">
                        <i class="bi bi-check-lg me-1"></i> Guardar producto
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-light border">Cancelar</a>
                </div>
            </div>

            {{-- ── Columna derecha: imágenes ───────────────────────── --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-semibold mb-0 small text-dark">
                            <i class="bi bi-images me-1 text-info"></i> Imágenes del producto
                        </h6>
                        <span class="text-muted" style="font-size:.68rem;">JPG · PNG · WEBP · máx. 5 MB</span>
                    </div>
                    <div class="card-body px-3 py-3">

                        {{-- Imágenes existentes (modo edición) --}}
                        @if(isset($product) && $product && isset($product->images) && $product->images->count())
                            <p class="small text-muted mb-2 fw-semibold">Imágenes actuales</p>
                            <div class="row g-2 mb-3">
                                @foreach($product->images->sortByDesc('is_primary') as $img)
                                    <div class="col-4" id="existingImg{{ $img->id }}">
                                        <div class="position-relative rounded-2 overflow-hidden border {{ $img->is_primary ? 'border-primary border-2' : 'border-light' }}"
                                             style="aspect-ratio:1;background:#f0f0f0;">
                                            <img src="{{ Storage::disk('public')->url($img->filename) }}"
                                                 class="w-100 h-100"
                                                 style="object-fit:cover;"
                                                 alt="{{ $img->original_name }}">

                                            {{-- Badge tipo --}}
                                            <span class="position-absolute top-0 start-0 m-1 badge {{ $img->is_primary ? 'bg-primary' : 'bg-secondary' }}"
                                                  style="font-size:.58rem;padding:2px 5px;">
                                                {{ $img->is_primary ? '★ Principal' : 'Común' }}
                                            </span>

                                            {{-- Acciones overlay --}}
                                            <div class="position-absolute bottom-0 start-0 end-0 d-flex gap-1 p-1"
                                                 style="background:linear-gradient(transparent,rgba(0,0,0,.55));">
                                                @if(!$img->is_primary)
                                                    <form action="{{ route('products.images.primary', [$product, $img]) }}"
                                                          method="POST" class="flex-fill">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn w-100 text-white"
                                                                style="font-size:.6rem;padding:2px 4px;background:rgba(13,110,253,.75);border:none;border-radius:4px;"
                                                                title="Marcar como principal">
                                                            <i class="bi bi-star-fill"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('products.images.destroy', [$product, $img]) }}"
                                                      method="POST" class="flex-fill"
                                                      onsubmit="return confirm('¿Eliminar esta imagen?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            class="btn w-100 text-white"
                                                            style="font-size:.6rem;padding:2px 4px;background:rgba(220,53,69,.75);border:none;border-radius:4px;"
                                                            title="Eliminar">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="text-muted text-truncate mt-1" style="font-size:.65rem;">{{ $img->original_name }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="small text-muted mb-2 fw-semibold">Agregar más imágenes</p>
                        @endif

                        {{-- Zona de carga --}}
                        <div id="dropzone"
                             class="rounded-3 text-center p-3 position-relative"
                             style="border:2px dashed #c8d3e0;background:#fafbff;min-height:140px;cursor:pointer;transition:border-color .15s,background .15s;">
                            <div id="dropzoneIdle" class="d-flex flex-column align-items-center justify-content-center" style="pointer-events:none;min-height:100px;">
                                <i class="bi bi-cloud-arrow-up text-info mb-2" style="font-size:2rem;"></i>
                                <div class="fw-semibold small text-dark">Arrastra imágenes aquí</div>
                                <div class="text-muted" style="font-size:.75rem;">o haz clic para seleccionar archivos</div>
                            </div>
                            <input type="file" name="images[]" id="imageInput"
                                   multiple accept="image/jpeg,image/png,image/webp,image/gif"
                                   class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                   style="cursor:pointer;">
                        </div>

                        {{-- Previews de imágenes nuevas --}}
                        <div id="previewSection" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                                <p class="small fw-semibold text-dark mb-0">
                                    Nuevas imágenes <span id="previewCount" class="badge bg-info text-white ms-1">0</span>
                                </p>
                                <button type="button" id="clearImages" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.72rem;">
                                    <i class="bi bi-x me-1"></i>Limpiar
                                </button>
                            </div>
                            <div id="previewGrid" class="row g-2"></div>
                            <div class="mt-2 rounded-2 px-2 py-1 border" style="background:#fffbe6;font-size:.7rem;">
                                <i class="bi bi-lightbulb me-1 text-warning"></i>
                                Haz clic en una imagen para marcarla como <strong>Principal</strong>
                            </div>
                        </div>

                        <input type="hidden" name="primary_image_index" id="primaryImageIndex" value="0">

                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@push('styles')
<style>
    #dropzone:hover, #dropzone.drag-over {
        border-color: #0d6efd !important;
        background: #f0f5ff !important;
    }
    .img-preview-card {
        aspect-ratio: 1;
        background: #f0f0f0;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color .15s, box-shadow .15s;
    }
    .img-preview-card.is-primary {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13,110,253,.25);
    }
    .img-preview-card img {
        width: 100%; height: 100%; object-fit: cover; display: block;
    }
    .img-preview-card .preview-badge {
        position: absolute; top: 4px; left: 4px;
        font-size: .58rem; padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
    }
    .img-preview-card .preview-remove {
        position: absolute; top: 4px; right: 4px;
        width: 20px; height: 20px;
        background: rgba(220,53,69,.8);
        border: none; border-radius: 50%;
        color: #fff; font-size: .65rem;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; opacity: 0; transition: opacity .15s;
    }
    .img-preview-card:hover .preview-remove { opacity: 1; }

    /* Select2 sm */
    .select2-container .select2-selection--single {
        height: calc(1.5em + .5rem + 2px) !important;
        font-size: .875rem;
        border: 1px solid #ced4da;
        border-radius: .25rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + .5rem) !important;
        padding-left: .5rem;
        font-size: .875rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + .5rem + 2px) !important;
    }
    .select2-dropdown { font-size: .875rem; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script>
(function () {
    // ── Select2 ───────────────────────────────────────────────────
    $(function () {
        $('.js-measurement-unit-select').select2({ width: '100%', placeholder: 'Seleccionar unidad...', allowClear: true });
    });

    // ── Margen costo/precio ───────────────────────────────────────
    const costInput  = document.getElementById('inputCost');
    const priceInput = document.getElementById('inputPrice');
    const marginEl   = document.getElementById('marginDisplay');

    function updateMargin() {
        const cost  = parseFloat(costInput?.value)  || 0;
        const price = parseFloat(priceInput?.value) || 0;
        if (!cost || !price) { marginEl.textContent = '—'; marginEl.className = 'fw-bold small'; return; }
        const pct = ((price - cost) / price * 100);
        marginEl.textContent = pct.toFixed(1) + '%';
        marginEl.className = 'fw-bold small ' + (pct >= 0 ? 'text-success' : 'text-danger');
    }
    costInput?.addEventListener('input', updateMargin);
    priceInput?.addEventListener('input', updateMargin);
    updateMargin();

    // ── Dropzone + previews ───────────────────────────────────────
    const dropzone    = document.getElementById('dropzone');
    const imageInput  = document.getElementById('imageInput');
    const previewSect = document.getElementById('previewSection');
    const previewGrid = document.getElementById('previewGrid');
    const previewCnt  = document.getElementById('previewCount');
    const primaryIdx  = document.getElementById('primaryImageIndex');
    const clearBtn    = document.getElementById('clearImages');

    let fileList = []; // array of File objects
    let primaryI = 0;

    // Drag visual feedback
    dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        addFiles([...e.dataTransfer.files]);
    });

    imageInput.addEventListener('change', function () {
        addFiles([...this.files]);
    });

    clearBtn?.addEventListener('click', () => {
        fileList = []; primaryI = 0;
        renderPreviews();
        syncInput();
    });

    function addFiles(newFiles) {
        const allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        newFiles.filter(f => allowed.includes(f.type) && f.size <= 5 * 1024 * 1024)
                .forEach(f => fileList.push(f));
        renderPreviews();
        syncInput();
    }

    function syncInput() {
        const dt = new DataTransfer();
        fileList.forEach(f => dt.items.add(f));
        imageInput.files = dt.files;
        primaryIdx.value = primaryI;
    }

    function renderPreviews() {
        previewGrid.innerHTML = '';
        if (!fileList.length) { previewSect.style.display = 'none'; return; }
        previewSect.style.display = '';
        previewCnt.textContent = fileList.length;

        fileList.forEach((file, idx) => {
            const col  = document.createElement('div');
            col.className = 'col-4';

            const card = document.createElement('div');
            card.className = 'img-preview-card' + (idx === primaryI ? ' is-primary' : '');

            // Image
            const img = document.createElement('img');
            const reader = new FileReader();
            reader.onload = e => img.src = e.target.result;
            reader.readAsDataURL(file);
            card.appendChild(img);

            // Badge
            const badge = document.createElement('span');
            badge.className = 'preview-badge ' + (idx === primaryI ? 'bg-primary text-white' : 'bg-secondary text-white bg-opacity-75');
            badge.textContent = idx === primaryI ? '★ Principal' : 'Común';
            card.appendChild(badge);

            // Remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'preview-remove';
            removeBtn.innerHTML = '×';
            removeBtn.addEventListener('click', e => {
                e.stopPropagation();
                fileList.splice(idx, 1);
                if (primaryI >= fileList.length) primaryI = 0;
                renderPreviews(); syncInput();
            });
            card.appendChild(removeBtn);

            // Nombre
            const name = document.createElement('div');
            name.className = 'text-muted text-truncate mt-1';
            name.style.fontSize = '.65rem';
            name.textContent = file.name;

            // Click → set primary
            card.addEventListener('click', () => {
                primaryI = idx;
                renderPreviews(); syncInput();
            });

            col.appendChild(card);
            col.appendChild(name);
            previewGrid.appendChild(col);
        });
    }
})();
</script>
@endpush
