<!-- Navrhované vylepšenia pre create/edit formuláre -->

<!-- 1. Pridať pole pre fotografie -->
<div class="mb-3">
    <label for="photos" class="form-label">Fotografie majetku</label>
    <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*">
    <small class="form-text text-muted">Môžete nahrať viacero fotografií (max 5MB každá)</small>
</div>

<!-- 2. Pridať pole pre záručné informácie -->
<div class="row">
    <div class="col-md-6">
        <label for="warranty_start" class="form-label">Začiatok záruky</label>
        <input type="date" class="form-control" id="warranty_start" name="warranty_start">
    </div>
    <div class="col-md-6">
        <label for="warranty_end" class="form-label">Koniec záruky</label>
        <input type="date" class="form-control" id="warranty_end" name="warranty_end">
    </div>
</div>

<!-- 3. Pridať pole pre služby a údržbu -->
<div class="mb-3">
    <label for="maintenance_schedule" class="form-label">Plán údržby</label>
    <select class="form-select" id="maintenance_schedule" name="maintenance_schedule">
        <option value="">Bez plánu údržby</option>
        <option value="monthly">Mesačne</option>
        <option value="quarterly">Štvrťročne</option>
        <option value="yearly">Ročne</option>
    </select>
</div>

<!-- 4. Pridať QR kód generátor -->
<div class="mb-3">
    <label class="form-label">QR kód</label>
    <div id="qr-code-container" class="border p-3 text-center">
        <p class="text-muted">QR kód sa vygeneruje po uložení</p>
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="print-qr">
        <i class="bi bi-printer"></i> Vytlačiť QR kód
    </button>
</div>