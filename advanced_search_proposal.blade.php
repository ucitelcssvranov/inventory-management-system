<!-- Návrh pokročilého vyhľadávacieho rozhrania -->
<div class="advanced-search-panel">
    <div class="row">
        <div class="col-md-3">
            <label class="form-label">Hodnota od-do</label>
            <div class="input-group">
                <input type="number" class="form-control" name="cost_from" placeholder="Od">
                <input type="number" class="form-control" name="cost_to" placeholder="Do">
            </div>
        </div>
        
        <div class="col-md-3">
            <label class="form-label">Dátum nadobudnutia</label>
            <div class="input-group">
                <input type="date" class="form-control" name="acquired_from">
                <input type="date" class="form-control" name="acquired_to">
            </div>
        </div>
        
        <div class="col-md-3">
            <label class="form-label">Záruka</label>
            <select class="form-select" name="warranty_status">
                <option value="">Všetko</option>
                <option value="valid">V záruke</option>
                <option value="expiring">Končí do 30 dní</option>
                <option value="expired">Po záruke</option>
            </select>
        </div>
        
        <div class="col-md-3">
            <label class="form-label">Posledná údržba</label>
            <select class="form-select" name="maintenance_status">
                <option value="">Všetko</option>
                <option value="due">Potrebuje údržbu</option>
                <option value="recent">Nedávno udržiavaný</option>
                <option value="never">Nikdy neudržiavaný</option>
            </select>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <label class="form-label">Tagy</label>
            <input type="text" class="form-control" name="tags" placeholder="IT, laptop, dell...">
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Vyhľadávanie v poznámkach</label>
            <input type="text" class="form-control" name="notes_search" placeholder="Hľadať v poznámkach...">
        </div>
    </div>
</div>