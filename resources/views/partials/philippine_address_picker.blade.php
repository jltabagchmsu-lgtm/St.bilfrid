@php
    $pickerPrefix = $prefix ?? 'create';
    $pickerLabel = $label ?? 'Site Location & Address (Philippine Standard)';
    $pickerVal = $currentValue ?? '';
@endphp

<div class="ph-address-picker-card" id="phAddrCard_{{ $pickerPrefix }}" style="background: #f8fafc; border: 1px solid var(--border-color, #e2e8f0); border-radius: var(--radius-sm, 6px); padding: 12px 14px; margin-bottom: 14px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
        <label class="form-label" style="font-size: 0.775rem; font-weight: 700; margin: 0; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
            <span>📍 {{ $pickerLabel }}</span>
            <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.65rem; padding: 2px 6px;">Zero-Typo Selector</span>
        </label>
        <button type="button" class="btn-secondary" id="btnToggleManual_{{ $pickerPrefix }}" style="font-size: 0.7rem; padding: 2px 8px; height: 24px; color: var(--text-secondary);" onclick="togglePhManualAddressMode('{{ $pickerPrefix }}')">
            ✏️ Manual Text Override
        </button>
    </div>

    <!-- Dropdown Cascading Selector Section -->
    <div id="phAddrDropdownsWrap_{{ $pickerPrefix }}">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 8px;">
            <div>
                <label style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 3px;">1. Region / Province <span style="color: var(--primary-red);">*</span></label>
                <select id="{{ $pickerPrefix }}_addr_province" class="form-select" style="font-size: 0.8rem; height: 36px; padding: 4px 8px;" onchange="onPhProvinceChange('{{ $pickerPrefix }}')">
                    <option value="">-- Select Province / Region --</option>
                </select>
            </div>
            <div>
                <label style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 3px;">2. City / Municipality <span style="color: var(--primary-red);">*</span></label>
                <select id="{{ $pickerPrefix }}_addr_city" class="form-select" style="font-size: 0.8rem; height: 36px; padding: 4px 8px;" onchange="onPhCityChange('{{ $pickerPrefix }}')">
                    <option value="">-- Select City First --</option>
                </select>
            </div>
            <div>
                <label style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 3px;">3. Barangay / District <span style="color: var(--primary-red);">*</span></label>
                <select id="{{ $pickerPrefix }}_addr_barangay" class="form-select" style="font-size: 0.8rem; height: 36px; padding: 4px 8px;" onchange="onPhBarangayChange('{{ $pickerPrefix }}')">
                    <option value="">-- Select Barangay First --</option>
                </select>
            </div>
        </div>

        <!-- Custom Barangay Input if not in predefined list -->
        <div id="phCustomBarangayWrap_{{ $pickerPrefix }}" style="display: none; margin-bottom: 8px;">
            <label style="font-size: 0.7rem; color: var(--primary-red); font-weight: 600; display: block; margin-bottom: 2px;">Enter Specific / Unlisted Barangay Name</label>
            <input type="text" id="{{ $pickerPrefix }}_addr_custom_barangay" class="form-input" placeholder="e.g. Barangay San Antonio Proper" style="font-size: 0.825rem; height: 36px;" oninput="updatePhFormattedAddress('{{ $pickerPrefix }}')">
        </div>

        <div style="margin-bottom: 6px;">
            <label style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 2px;">
                4. Street Name, Building / Unit, Lot & Block, Subdivision, or Commercial District
            </label>
            <input type="text" id="{{ $pickerPrefix }}_addr_street" class="form-input" placeholder="e.g. North Triangle Commercial District, Unit 1402 Tower B, Lot 12 Blk 4" style="font-size: 0.825rem; height: 36px;" oninput="updatePhFormattedAddress('{{ $pickerPrefix }}')">
        </div>
    </div>

    <!-- Manual Text Mode (Hidden by default, activated via override button) -->
    <div id="phAddrManualWrap_{{ $pickerPrefix }}" style="display: none; margin-bottom: 6px;">
        <label style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 2px;">Direct Full Address String</label>
        <input type="text" id="{{ $pickerPrefix }}_addr_manual_input" class="form-input" placeholder="e.g. North Triangle Commercial District, Quezon City, Metro Manila" style="font-size: 0.825rem; height: 36px;" oninput="onPhManualInput('{{ $pickerPrefix }}')">
    </div>

    <!-- Actual Hidden Form Input submitted to Server -->
    <input type="hidden" name="location" id="{{ $pickerPrefix }}_location" value="{{ $pickerVal }}">

    <!-- Live Formatted Address Badge & Visual Confirmation -->
    <div style="font-size: 0.75rem; color: var(--text-secondary); background: #ffffff; padding: 7px 12px; border-radius: 4px; border: 1px dashed var(--border-color); display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 4px;">
        <div style="display: flex; align-items: center; gap: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            <span style="color: var(--primary-red); font-weight: 700; flex-shrink: 0;">📍 Standardized:</span>
            <span id="{{ $pickerPrefix }}_addr_preview_text" style="font-weight: 600; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.775rem;">
                {{ !empty($pickerVal) ? $pickerVal : 'Please select City and Barangay above' }}
            </span>
        </div>
        <span class="badge" id="{{ $pickerPrefix }}_addr_status_badge" style="font-size: 0.65rem; padding: 1px 6px; background: rgba(56, 189, 248, 0.12); color: #0284c7; border: 1px solid rgba(56, 189, 248, 0.3); flex-shrink: 0;">
            {{ !empty($pickerVal) ? 'Saved' : 'Dropdown Ready' }}
        </span>
    </div>
</div>

@if(!defined('PH_ADDRESS_PICKER_SCRIPT_LOADED'))
    @php define('PH_ADDRESS_PICKER_SCRIPT_LOADED', true); @endphp
    <script>
    const PH_LOCATIONS_DATA = {
        "Metro Manila (NCR)": {
            "Quezon City": [
                "North Triangle", "South Triangle", "Diliman", "Batasan Hills", "Commonwealth", 
                "Cubao (Socorro)", "Loyola Heights", "Fairview", "Tandang Sora", "Bagong Pag-asa", 
                "New Manila", "Matandang Balara", "Holy Spirit", "Santa Mesa Heights", "Project 6", 
                "Project 8", "Novaliches Proper", "Greater Lagro", "Pasong Tamo", "UP Campus", 
                "West Triangle", "Veterans Village", "E. Rodriguez", "Kamuning", "San Martin de Porres"
            ],
            "Makati City": [
                "Bel-Air", "San Lorenzo", "Legazpi Village", "Salcedo Village", "Poblacion", 
                "Guadalupe Nuevo", "Guadalupe Viejo", "Bangkal", "Magallanes", "Palanan", 
                "Pio del Pilar", "Cembo", "Pembo", "Comembo", "San Antonio", "Tejeros", 
                "Singkamas", "Carmona", "Valenzuela", "Urdaneta", "Dasmariñas Village", "Forbes Park"
            ],
            "Taguig City": [
                "Fort Bonifacio / BGC", "Pinagsama", "Western Bicutan", "Upper Bicutan", 
                "Lower Bicutan", "Ususan", "Bambang", "Calzada", "Hagonoy", "Ibayo-Tipas", 
                "Ligid-Tipas", "Maharlika Village", "Napindan", "Palingon", "San Miguel", 
                "Santa Ana", "Tuktukan", "Central Signal Village", "Bagumbayan", "Katuparan"
            ],
            "City of Manila": [
                "Binondo", "Ermita", "Malate", "Paco", "Pandacan", "Port Area", "Quiapo", 
                "Sampaloc", "San Andres", "San Miguel", "San Nicolas", "Santa Ana", 
                "Santa Cruz", "Santa Mesa", "Tondo 1st District", "Tondo 2nd District", "Intramuros"
            ],
            "Pasig City": [
                "Ortigas Center / San Antonio", "Kapitolyo", "Ugong", "Maybunga", "Rosario", 
                "Bagong Ilog", "Caniogan", "Manggahan", "Pinagbuhatan", "Santolan", 
                "Santa Lucia", "Bambang", "Dela Paz", "Palatiw", "San Nicolas", "San Joaquin", "Buting"
            ],
            "Mandaluyong City": [
                "Wack-Wack Greenhills", "Highway Hills", "Addition Hills", "Plainview", 
                "Barangka Ilaya", "Barangka Itaas", "Barangka Ibaba", "Barangka Drive", 
                "Buayang Bato", "Hulo", "Malamig", "Pleasant Hills", "Vergara", "Mauway", "Namayan"
            ],
            "Parañaque City": [
                "BF Homes", "San Antonio", "Don Bosco", "Moonwalk", "Sun Valley", "Baclaran", 
                "Tambo", "La Huerta", "San Dionisio", "Santo Niño", "Marcelo Green", "San Martin de Porres", "Don Galo"
            ],
            "Pasay City": [
                "Baclaran", "Malibay", "Maricaban", "San Isidro", "San Jose", "San Rafael", 
                "San Roque", "Santa Clara", "Santo Niño", "Villamor Airbase", "MOA Complex / Bay City"
            ],
            "Las Piñas City": [
                "BF Resort Village", "Almanza Uno", "Almanza Dos", "Pamplona Uno", "Pamplona Dos", 
                "Pamplona Tres", "Talon Uno", "Talon Dos", "Talon Tres", "Talon Kuatro", "Talon Singko", 
                "Pulang Lupa Uno", "Pulang Lupa Dos", "Zapote", "Daniel Fajardo", "Manuyo Uno", "Manuyo Dos"
            ],
            "Muntinlupa City": [
                "Alabang (Filinvest & Madrigal)", "Ayala Alabang", "Cupang", "Buli", "Sucat", 
                "Putatan", "Poblacion", "Tunasan", "Bayanan"
            ],
            "Marikina City": [
                "Concepcion Uno", "Concepcion Dos", "Marikina Heights", "Santo Niño", "Santa Elena", 
                "San Roque", "Barangka", "Calumpang", "Industrial Valley", "Fortune", "Malanday", "Parang", "Tumana"
            ],
            "San Juan City": [
                "Greenhills", "Addition Hills", "Little Baguio", "Pasadena", "Corazon de Jesus", 
                "Batis", "Kabayanan", "Maytunas", "Onse", "Tibagan", "West Crame", "Salapan", "San Perfecto"
            ],
            "Caloocan City": [
                "Grace Park East", "Grace Park West", "Bagong Barrio", "Morning Breeze", 
                "University Hills", "Bagumbong", "Camarin", "Deparo", "Bagong Silang", "Tala"
            ],
            "Valenzuela City": [
                "Karuhatan", "Malinta", "Marulas", "Maysan", "Paso de Blas", "Ugong", 
                "Gen. T. de Leon", "Mapulang Lupa", "Bignay", "Canumay East", "Canumay West", "Lingunan"
            ],
            "Malabon City": [
                "Catmon", "Concepcion", "Flores", "Hulong Duhat", "Maysilo", "Panghulo", 
                "Potrero", "San Agustin", "Tañong", "Tinajeros", "Tonsuya", "Tugatog"
            ],
            "Navotas City": [
                "Bagumbayan North", "Bagumbayan South", "Bangculasi", "Daanghari", "Navotas East", 
                "Navotas West", "North Bay Boulevard", "San Jose", "San Roque", "Tangos", "Tanza"
            ],
            "Municipality of Pateros": [
                "Aguho", "Magtanggol", "Martires del 96", "Poblacion", "San Pedro", "San Roque", 
                "Santa Ana", "Santo Rosario-Kanluran", "Santo Rosario-Silangan", "Tabacalera"
            ]
        },
        "Negros Occidental": {
            "Bacolod City": [
                "Alijis", "Banago", "Bata", "Cabug", "Estefania", "Felisa", "Granada", 
                "Handumanan", "Mandalagan", "Mansilingan", "Montevista", "Pahanocoy", 
                "Punta Taytay", "Singcang-Airport", "Sum-ag", "Taculing", "Tangub", 
                "Villamonte", "Vista Alegre", "Barangay 1", "Barangay 2", "Barangay 3", 
                "Barangay 4", "Barangay 5", "Barangay 6", "Barangay 7", "Barangay 8", 
                "Barangay 9", "Barangay 10", "Barangay 11", "Barangay 12", "Barangay 13", 
                "Barangay 14", "Barangay 15", "Barangay 16", "Barangay 17", "Barangay 18", 
                "Barangay 19", "Barangay 20", "Barangay 21", "Barangay 22", "Barangay 23", 
                "Barangay 24", "Barangay 25", "Barangay 26", "Barangay 27", "Barangay 28", 
                "Barangay 29", "Barangay 30", "Barangay 31", "Barangay 32", "Barangay 33", 
                "Barangay 34", "Barangay 35", "Barangay 36", "Barangay 37", "Barangay 38", 
                "Barangay 39", "Barangay 40"
            ],
            "Talisay City": [
                "Zone 1", "Zone 2", "Zone 3", "Zone 4", "Zone 5", "Zone 6", "Zone 7", "Zone 8", 
                "Zone 9", "Zone 10", "Zone 11", "Zone 12", "Zone 13", "Zone 14", "Zone 15", "Zone 16", 
                "Matab-ang", "Dos Hermanas", "Concepcion", "Bubog", "Efigenio Lizares", "San Fernando"
            ],
            "Silay City": [
                "Barangay 1 (Poblacion)", "Barangay 2 (Poblacion)", "Barangay 3 (Poblacion)", 
                "Barangay 4 (Poblacion)", "Barangay 5 (Poblacion)", "Barangay 6 (Poblacion)", 
                "Bagtic", "Balaring", "E. Lopez", "Guinhalaran", "Kapitan Ramon", "Lantad", "Mambulac", "Patag", "Rizal"
            ],
            "Bago City": [
                "Balingasag", "Calumangan", "Caridad", "Dulao", "Ilijan", "Lag-asan", "Ma-ao", 
                "Mailum", "Malingin", "Napoles", "Pacol", "Poblacion", "Sagasa", "Taloc"
            ],
            "Cadiz City": ["Cabahug", "Cadiz Viejo", "Daga", "Jerusalem", "Mabini", "Poblacion", "Sicaba", "Tiglawigan", "Tinampa-an"],
            "Sagay City": ["Bulanon", "Fabrica", "General Luna", "Old Sagay", "Poblacion I", "Poblacion II", "Rizal", "Vito"],
            "San Carlos City": ["Barangay 1", "Barangay 2", "Barangay 3", "Barangay 4", "Barangay 5", "Barangay 6", "Buluangan", "Palampas", "Rizal", "San Juan"],
            "Victorias City": ["Barangay I", "Barangay II", "Barangay III", "Barangay IV", "Barangay V", "Barangay VI", "Barangay VII", "Barangay VIII", "Barangay IX", "Barangay X", "Barangay XI", "Barangay XII", "Barangay XIII", "Barangay XIV", "Barangay XV", "Barangay XVI", "Barangay XVII", "Barangay XVIII", "Barangay XIX", "Barangay XX", "Barangay XXI"]
        },
        "Cebu": {
            "Cebu City": [
                "Lahug", "Apas (IT Park)", "Banilad", "Mabolo", "Kasambagan", "Guadalupe", 
                "Cebu Business Park / Luz", "Capitol Site", "Sambag I", "Sambag II", "Pardo", 
                "Talamban", "Tisa", "Labangon", "Basak San Nicolas", "Basak Pardo", "Punta Princesa", 
                "Bulacao", "Kamputhaw", "Carreta", "Zapatera", "San Antonio", "Pari-an", "Tejero"
            ],
            "Mandaue City": [
                "Bakilid", "Banilad", "Cabancalan", "Centro", "Guizo", "Maguikay", 
                "Subangdaku", "Tipolo", "Umapad", "Alang-alang", "Casuntingan", "Labogon", "Looc", "Pakna-an", "Pagsabungan"
            ],
            "Lapu-Lapu City": [
                "Mactan", "Maribago", "Marigondon", "Agus", "Basak", "Gun-ob", "Ibo", 
                "Pajac", "Poblacion", "Pusok", "Subabasbas", "Babag", "Bankal", "Buaya", "Canjulao"
            ],
            "Talisay City": [
                "Bulacao", "Cansojong", "Dumlog", "Lawaan I", "Lawaan II", "Lawaan III", 
                "Mohon", "Poblacion", "San Isidro", "San Roque", "Tabunok", "Tangke", "Pooc"
            ],
            "Consolacion": ["Cabangahan", "Cansaga", "Casili", "Danlag", "Jugan", "Lamac", "Nangka", "Pitogo", "Poblacion Occidental", "Poblacion Oriental", "Tayud", "Tugbongan"],
            "Liloan": ["Catarman", "Cotcot", "Jubay", "Poblacion", "San Roque", "San Vicente", "Santa Cruz", "Tayud", "Yati"],
            "Minglanilla": ["Cadulawan", "Calajo-an", "Camp 7", "Camp 8", "Linao", "Manduang", "Pakigne", "Poblacion Ward 1", "Poblacion Ward 2", "Tubod", "Tulay", "Tungkop"]
        },
        "Cavite": {
            "Bacoor City": ["Habay I", "Habay II", "Molino I", "Molino II", "Molino III", "Molino IV", "Molino V", "Molino VI", "Molino VII", "Niog I", "Niog II", "Panapaan", "Queens Row Central", "Queens Row East", "Queens Row West", "Salinas", "San Nicolas I", "San Nicolas II", "Talaba", "Zapote"],
            "Imus City": ["Anabu I-A", "Anabu I-B", "Anabu II-A", "Anabu II-B", "Bayan Luma", "Bucandala", "Carsadang Bago", "Malagasang I-A", "Malagasang I-B", "Malagasang II-A", "Malagasang II-B", "Medicion", "Poblacion", "Tanzang Luma", "Toclong"],
            "Dasmariñas City": ["Burol", "Fatima", "H-2", "Langkaan I", "Langkaan II", "Paliparan I", "Paliparan II", "Paliparan III", "Salawag", "Salitran I", "Salitran II", "Salitran III", "Salitran IV", "Sampaloc I", "Sampaloc II", "San Agustin I", "San Agustin II", "San Agustin III", "Zone I", "Zone II", "Zone III", "Zone IV"],
            "Tagaytay City": ["Asisan", "Bagong Tubig", "Calabuso", "Dapdap West", "Dapdap East", "Iruhin Central", "Iruhin East", "Iruhin West", "Kaybagal Central", "Kaybagal North", "Kaybagal South", "Mag-Asawang Ilat", "Maharlika East", "Maharlika West", "Mendez Crossing East", "Mendez Crossing West", "Neogan", "Patutong Malaki North", "Patutong Malaki South", "Sambong", "San Jose", "Silang Junction North", "Silang Junction South", "Sungay North", "Sungay South", "Tolentino East", "Tolentino West"],
            "General Trias City": ["Bacao I", "Bacao II", "Buenavista I", "Buenavista II", "Manggahan", "Navarro", "Pasong Camachile I", "Pasong Camachile II", "Pasong Kawayan I", "Pasong Kawayan II", "San Francisco", "Santa Clara", "Tejero"],
            "Silang": ["Acacia", "Balite I", "Balite II", "Biga I", "Biga II", "Bulihan", "Iba", "Kaong", "Lalaan I", "Lalaan II", "Lucsuhin", "Poblacion", "Pooc", "Tartaria", "Tubuan"],
            "Carmona City": ["Bancal", "Cabilang Baybay", "Lantic", "Mabuhay", "Maduya", "Milagrosa", "Poblacion 1", "Poblacion 2", "Poblacion 3", "Poblacion 4", "Poblacion 5", "Poblacion 6", "Poblacion 7", "Poblacion 8"],
            "Trece Martires City": ["Cabezas", "Cabuco", "Conchu", "De Ocampo", "Gregorio", "Inocencio", "Lallana", "Lapidario", "Luciano", "Osorio", "Perez", "San Agustin", "Sunshine"]
        },
        "Laguna": {
            "Santa Rosa City": ["Aplaya", "Balibago", "Caingin", "Dila", "Dita", "Don Jose", "Ibaba", "Labas", "Macabling", "Malitlit", "Malusak", "Market Area", "Kanluran", "Poblacion", "Pulong Santa Cruz", "Santo Domingo", "Sinalhan", "Tagapo"],
            "Calamba City": ["Bagong Kalsada", "Bañadero", "Banlic", "Barandal", "Batino", "Bucal", "Bunggo", "Burol", "Canlubang", "Halang", "Lawa", "Lecheria", "Lingga", "Looc", "Mabuhay", "Majada Labas", "Makiling", "Mapagong", "Mayapa", "Paciano Rizal", "Palingon", "Palo-Alto", "Pansol", "Parian", "Prinzza", "Punta", "Real", "Saimsim", "Sampiruhan", "San Cristobal", "San Jose", "San Juan", "Sirang Lupa", "Sucol", "Turbina", "Ulango"],
            "Biñan City": ["Biñan (Poblacion)", "Bungahan", "Canlalay", "Casile", "De La Paz", "Ganado", "Langkiwa", "Loma", "Malaban", "Mamplasan", "Platero", "Poblacion", "San Antonio", "San Francisco", "San Jose", "San Vicente", "Santo Niño", "Santo Tomas", "Sorot-sorot", "Timbao", "Tubigan", "Zapote"],
            "Cabuyao City": ["Baclaran", "Banaybanay", "Banlic", "Bigaa", "Buting", "Casile", "Diezmo", "Gulod", "Mamatid", "Marinig", "Niugan", "Pittland", "Pulo", "Sala", "San Isidro"],
            "San Pedro City": ["Calendola", "Chrysanthemum", "Cuyab", "Estrella", "Fatima", "G.S.I.S.", "Landayan", "Langgam", "Laram", "Magsaysay", "Maharlika", "Narra", "Nueva", "Pacita 1", "Pacita 2", "Poblacion", "Riverside", "Rosario", "Sampaguita Village", "San Antonio", "San Roque", "San Vicente", "Santo Niño", "United Bayanihan", "United Better Living", "Vicente Leycosa", "Viernes"]
        },
        "Rizal": {
            "Antipolo City": ["Bagong Nayon", "Beverly Hills", "Calawis", "Cupang", "Dalig", "Dela Paz", "Inarawan", "Mambugan", "Mayamot", "Muntindilaw", "San Isidro", "San Jose", "San Juan", "San Luis", "San Roque", "Santa Cruz"],
            "Cainta": ["San Andres", "San Isidro", "San Juan", "San Roque", "Santa Rosa", "Santo Domingo", "Santo Niño"],
            "Taytay": ["Dolores", "Muzon", "San Isidro", "San Juan", "Santa Ana"],
            "San Mateo": ["Ampid I", "Ampid II", "Banaba", "Dulong Bayan 1", "Dulong Bayan 2", "Guinayang", "Guitnang Bayan 1", "Guitnang Bayan 2", "Malanday", "Maly", "Pintong Bukawe", "Santa Ana", "Silangan"],
            "Rodriguez (Montalban)": ["Balite", "Burgos", "Geronimo", "Macabud", "Manggahan", "Mascap", "Puray", "Rosario", "San Isidro", "San Jose", "San Rafael"]
        },
        "Bulacan": {
            "City of San Jose del Monte": ["Ciudad Real", "Dulong Bayan", "Fatima", "Francisco Homes", "Gaya-gaya", "Graceville", "Kaybanban", "Kaypian", "Maharlika", "Muzon", "Paradise III", "Poblacion", "San Manuel", "San Martin", "San Pedro", "San Rafael", "San Roque", "Santa Cruz", "Santo Cristo", "Sapang Palay", "Tungkong Mangga"],
            "Malolos City": ["Anilao", "Atlag", "Babatnin", "Bagna", "Balayong", "Balite", "Bangkal", "Barihan", "Bulihan", "Caingin", "Calero", "Canalate", "Catmon", "Cofradia", "Dakila", "Guinhawa", "Ligas", "Liang", "Look 1st", "Look 2nd", "Lugam", "Mabolo", "Mambog", "Masile", "Matimbo", "Mojon", "Namayan", "Niugan", "Pamarawan", "Panasahan", "Pinagbakahan", "San Agustin", "San Gabriel", "San Juan", "San Pablo", "San Vicente", "Santa Isabel", "Santo Cristo", "Santo Niño", "Santo Rosario", "Santor", "Sumapang Bata", "Sumapang Matanda", "Taal", "Tikay"],
            "Meycauayan City": ["Bagbaguin", "Bahay Pare", "Bancal", "Banga", "Bayugo", "Caingin", "Calvario", "Camalig", "Hulo", "Iba", "Langka", "Lawa", "Libtong", "Liputan", "Malhacan", "Pajac", "Perez", "Poblacion", "Saluysoy", "Tugatog", "Ubihan", "Zamora"],
            "Santa Maria": ["Bagbaguin", "Balasing", "Buenavista", "Bulac", "Camangyanan", "Catmon", "Caypombo", "Caysio", "Guyong", "Lalangan", "Mag-asawang Sapa", "Mahabang Parang", "Manggahan", "Parada", "Poblacion", "Pulong Buhangin", "San Gabriel", "San Jose Patag", "San Vicente", "Santa Clara", "Santa Cruz", "Silangan", "Tabing Bakod", "Tumana"]
        },
        "Pampanga": {
            "Angeles City": ["Agapito del Rosario", "Amsic", "Anunas", "Balibago", "Capaya", "Claro M. Recto", "Cuayan", "Cutcut", "Cutud", "Lourdes North West", "Lourdes Sur", "Lourdes Sur East", "Malabanias", "Margot", "Mining", "Ninoy Aquino", "Pampang", "Pandan", "Pulung Maragul", "Pulungbulu", "Pulung Cacutud", "Salapungan", "San Jose", "San Nicolas", "Santa Teresita", "Santa Trinidad", "Santo Cristo", "Santo Domingo", "Santo Rosario", "Sapalibutad", "Sapangbato", "Tabun", "Virgen Delos Remedios"],
            "City of San Fernando": ["Alasap", "Bulaon", "Calulut", "Del Carmen", "Del Pilar", "Del Rosario", "Dela Paz Norte", "Dela Paz Sur", "Dolores", "Juliana", "Lara", "Lourdes", "Magliman", "Maimpis", "Malino", "Malpitic", "Pandaras", "Panipuan", "Quebiauan", "Saguin", "San Agustin", "San Felipe", "San Isidro", "San Jose", "San Juan", "San Nicolas", "San Pedro", "Santa Lucia", "Santa Teresita", "Santo Niño", "Santo Rosario", "Sindalan", "Telabastagan"],
            "Mabalacat City / Clark": ["Atlu-Bola", "Bical", "Bundagul", "Cacutud", "Calumpang", "Camachiles", "Clark Freeport Zone", "Clarkview", "Dapdap", "Dau", "Dolores", "Duquit", "Lakandula", "Mabiga", "Macapagal Village", "Mamatitang", "Mangalit", "Marcos Village", "Mawaque", "Paralayunan", "Poblacion", "San Francisco", "San Joaquin", "Santa Ines", "Santa Maria", "Santo Rosario", "Sapang Balen", "Sapang Biabas", "Tabun"]
        },
        "Batangas": {
            "Batangas City": ["Alangilan", "Balagtas", "Balete", "Bolbok", "Calicanto", "Cuta", "Gulod Itaas", "Gulod Labac", "Kumintang Ibaba", "Kumintang Ilaya", "Libjo", "Pallocan Kanluran", "Pallocan Silangan", "Poblacion 1 to 24", "San Isidro", "Santa Clara", "Santa Rita Aplaya", "Santa Rita Karsada", "Tabangao Aplaya", "Tinga Itaas", "Tinga Labac", "Wawa"],
            "Lipa City": ["Antipolo del Norte", "Antipolo del Sur", "Bagong Pook", "Balintawak", "Banaybanay", "Bolbok", "Bulacnin", "Calamias", "Dagatan", "Halang", "Inosluban", "Latag", "Lodlod", "Lumbang", "Marawoy", "Mataas na Lupa", "Munting Pulo", "Pinagtongulan", "Poblacion 1 to 12", "Sabang", "Sampaguita", "San Carlos", "San Celestino", "San Lucas", "San Salvador", "Santo Niño", "Santo Toribio", "Tambo", "Tangway", "Tibig", "Tipacan"],
            "Tanauan City": ["Bagbag", "Bagumbayan", "Balele", "Banjo East", "Banjo West", "Bilog-bilog", "Boot", "Darasa", "Hidalgo", "Janopol", "Laurel", "Malaking Pulo", "Natatas", "Pagaspas", "Pantay Bata", "Pantay Matanda", "Poblacion 1 to 7", "Sala", "Sambat", "San Jose", "Santor", "Trapiche", "Ulango"],
            "Santo Tomas City": ["San Antonio", "San Bartolome", "San Felix", "San Fernando", "San Francisco", "San Isidro Norte", "San Isidro Sur", "San Joaquin", "San Jose", "San Juan", "San Luis", "San Miguel", "San Pedro", "San Rafael", "San Roque", "San Vicente", "Santa Ana", "Santa Anastacia", "Santa Clara", "Santa Cruz", "Santa Elena", "Santa Maria", "Santa Marta", "Santa Teresita", "Santiago", "Poblacion 1 to 4"]
        },
        "Davao del Sur": {
            "Davao City": [
                "Poblacion", "Bajada", "Buhangin", "Matina Crossing", "Matina Aplaya", "Lanang", 
                "Toril", "Calinan", "Agdao", "Talomo", "Sasa", "Mintal", "Ma-a", "Ecoland", 
                "Cabantian", "Indangan", "Tibungco", "Panacan", "Bago Aplaya", "Bangkal", "Catalunan Grande", "Catalunan Pequeño"
            ],
            "Digos City": ["Aplaya", "Balabag", "Colorado", "Cogon", "Dulangan", "Kapatagan", "Matti", "Poblacion", "San Jose", "San Miguel", "San Roque", "Sinawilan", "Tres de Mayo", "Zone 1 to 3"]
        },
        "Iloilo": {
            "Iloilo City": [
                "City Proper", "Jaro", "Mandurriao", "La Paz", "Molo", "Arevalo", "Lapuz", 
                "San Rafael", "Bolilao", "Bakhaw", "Tabuc Suba", "Calumpang", "Sambag", "Sooc", "Cubay"
            ]
        },
        "Benguet": {
            "Baguio City": [
                "Camp 7", "Loakan Proper", "Session Road / DPS", "Mines View Park", "Burnham-Legarda", 
                "Pacdal", "Guisad Central", "Bakakeng Central", "Bakakeng Norte", "Irisan", 
                "Aurora Hill", "Trancoville", "Cabinet Hill", "Lualhati", "Outlook Drive", "Greenwater Village"
            ],
            "La Trinidad": ["Balili", "Beckel", "Betag", "Poblacion", "Puguis", "Shilan", "Tawang", "Wangal"]
        },
        "Misamis Oriental": {
            "Cagayan de Oro City": ["Carmen", "Macasandig", "Nazareth", "Lapasan", "Kauswagan", "Puerto", "Bulua", "Gusa", "Camaman-an", "Patag", "Iponan", "Poblacion 1 to 40"]
        },
        "Other Provinces & Regions": {
            "Custom City / Municipality": ["Custom Barangay / Area"]
        }
    };

    function initPhAddressPicker(prefix, initialAddress = '') {
        const provSelect = document.getElementById(prefix + '_addr_province');
        if (!provSelect) return;

        provSelect.innerHTML = '<option value="">-- 1. Select Region / Province --</option>';
        Object.keys(PH_LOCATIONS_DATA).forEach(prov => {
            const opt = document.createElement('option');
            opt.value = prov;
            opt.textContent = prov;
            provSelect.appendChild(opt);
        });

        if (initialAddress && initialAddress.trim() !== '') {
            setPhAddress(prefix, initialAddress);
        } else {
            provSelect.value = 'Metro Manila (NCR)';
            onPhProvinceChange(prefix);
        }
    }

    function onPhProvinceChange(prefix) {
        const provSelect = document.getElementById(prefix + '_addr_province');
        const citySelect = document.getElementById(prefix + '_addr_city');
        const brgySelect = document.getElementById(prefix + '_addr_barangay');
        if (!provSelect || !citySelect || !brgySelect) return;

        const prov = provSelect.value;
        citySelect.innerHTML = '<option value="">-- 2. Select City / Municipality --</option>';
        brgySelect.innerHTML = '<option value="">-- 3. Select Barangay First --</option>';

        const customBrgyWrap = document.getElementById('phCustomBarangayWrap_' + prefix);
        if (customBrgyWrap) customBrgyWrap.style.display = 'none';

        if (prov && PH_LOCATIONS_DATA[prov]) {
            Object.keys(PH_LOCATIONS_DATA[prov]).forEach(city => {
                const opt = document.createElement('option');
                opt.value = city;
                opt.textContent = city;
                citySelect.appendChild(opt);
            });
            citySelect.disabled = false;
        } else {
            citySelect.disabled = true;
            brgySelect.disabled = true;
        }

        updatePhFormattedAddress(prefix);
    }

    function onPhCityChange(prefix) {
        const provSelect = document.getElementById(prefix + '_addr_province');
        const citySelect = document.getElementById(prefix + '_addr_city');
        const brgySelect = document.getElementById(prefix + '_addr_barangay');
        if (!provSelect || !citySelect || !brgySelect) return;

        const prov = provSelect.value;
        const city = citySelect.value;
        brgySelect.innerHTML = '<option value="">-- 3. Select Barangay / District --</option>';

        const customBrgyWrap = document.getElementById('phCustomBarangayWrap_' + prefix);
        if (customBrgyWrap) customBrgyWrap.style.display = 'none';

        if (prov && city && PH_LOCATIONS_DATA[prov] && PH_LOCATIONS_DATA[prov][city]) {
            const list = PH_LOCATIONS_DATA[prov][city];
            list.forEach(brgy => {
                const opt = document.createElement('option');
                opt.value = brgy;
                opt.textContent = brgy;
                brgySelect.appendChild(opt);
            });

            // Add Custom / Unlisted Option
            const otherOpt = document.createElement('option');
            otherOpt.value = '__custom__';
            otherOpt.textContent = '➕ Other / Unlisted Barangay...';
            brgySelect.appendChild(otherOpt);

            brgySelect.disabled = false;
        } else {
            brgySelect.disabled = true;
        }

        updatePhFormattedAddress(prefix);
    }

    function onPhBarangayChange(prefix) {
        const brgySelect = document.getElementById(prefix + '_addr_barangay');
        const customBrgyWrap = document.getElementById('phCustomBarangayWrap_' + prefix);
        const customBrgyInput = document.getElementById(prefix + '_addr_custom_barangay');
        
        if (brgySelect && brgySelect.value === '__custom__') {
            if (customBrgyWrap) customBrgyWrap.style.display = 'block';
            if (customBrgyInput) customBrgyInput.focus();
        } else {
            if (customBrgyWrap) customBrgyWrap.style.display = 'none';
        }

        updatePhFormattedAddress(prefix);
    }

    function updatePhFormattedAddress(prefix) {
        const provSelect = document.getElementById(prefix + '_addr_province');
        const citySelect = document.getElementById(prefix + '_addr_city');
        const brgySelect = document.getElementById(prefix + '_addr_barangay');
        const customBrgyInput = document.getElementById(prefix + '_addr_custom_barangay');
        const streetInput = document.getElementById(prefix + '_addr_street');
        const hiddenLocation = document.getElementById(prefix + '_location');
        const previewText = document.getElementById(prefix + '_addr_preview_text');
        const statusBadge = document.getElementById(prefix + '_addr_status_badge');

        if (!hiddenLocation) return;

        const prov = provSelect ? provSelect.value.trim() : '';
        const city = citySelect ? citySelect.value.trim() : '';
        let brgy = brgySelect ? brgySelect.value.trim() : '';
        if (brgy === '__custom__' && customBrgyInput) {
            brgy = customBrgyInput.value.trim();
        }
        const street = streetInput ? streetInput.value.trim() : '';

        const parts = [];
        if (street) parts.push(street);
        if (brgy) parts.push('Brgy. ' + brgy.replace(/^Brgy\.?\s*/i, ''));
        if (city) parts.push(city);
        if (prov && prov !== 'Other Provinces & Regions') parts.push(prov);

        const formatted = parts.join(', ');
        hiddenLocation.value = formatted;

        if (previewText) {
            previewText.textContent = formatted || 'Please select City and Barangay above';
        }
        if (statusBadge) {
            if (city && (brgy || street)) {
                statusBadge.textContent = '✓ Verified';
                statusBadge.style.background = 'rgba(16, 185, 129, 0.15)';
                statusBadge.style.color = '#059669';
                statusBadge.style.borderColor = 'rgba(16, 185, 129, 0.3)';
            } else {
                statusBadge.textContent = 'Incomplete';
                statusBadge.style.background = 'rgba(245, 158, 11, 0.15)';
                statusBadge.style.color = '#d97706';
                statusBadge.style.borderColor = 'rgba(245, 158, 11, 0.3)';
            }
        }
    }

    function togglePhManualAddressMode(prefix) {
        const dropdownsWrap = document.getElementById('phAddrDropdownsWrap_' + prefix);
        const manualWrap = document.getElementById('phAddrManualWrap_' + prefix);
        const manualInput = document.getElementById(prefix + '_addr_manual_input');
        const toggleBtn = document.getElementById('btnToggleManual_' + prefix);
        const hiddenLocation = document.getElementById(prefix + '_location');
        const statusBadge = document.getElementById(prefix + '_addr_status_badge');

        if (!dropdownsWrap || !manualWrap) return;

        const isCurrentlyManual = (manualWrap.style.display !== 'none');

        if (isCurrentlyManual) {
            // Switch to dropdown mode
            manualWrap.style.display = 'none';
            dropdownsWrap.style.display = 'block';
            if (toggleBtn) toggleBtn.innerHTML = '✏️ Manual Text Override';
            updatePhFormattedAddress(prefix);
        } else {
            // Switch to manual mode
            dropdownsWrap.style.display = 'none';
            manualWrap.style.display = 'block';
            if (toggleBtn) toggleBtn.innerHTML = '📋 Switch to Dropdowns';
            if (manualInput && hiddenLocation) {
                manualInput.value = hiddenLocation.value || '';
                manualInput.focus();
            }
            if (statusBadge) {
                statusBadge.textContent = 'Manual Text';
                statusBadge.style.background = '#f1f5f9';
                statusBadge.style.color = '#475569';
                statusBadge.style.borderColor = '#cbd5e1';
            }
        }
    }

    function onPhManualInput(prefix) {
        const manualInput = document.getElementById(prefix + '_addr_manual_input');
        const hiddenLocation = document.getElementById(prefix + '_location');
        const previewText = document.getElementById(prefix + '_addr_preview_text');

        if (!manualInput || !hiddenLocation) return;
        const val = manualInput.value.trim();
        hiddenLocation.value = val;
        if (previewText) {
            previewText.textContent = val || 'No address specified';
        }
    }

    function setPhAddress(prefix, fullAddress) {
        const hiddenLocation = document.getElementById(prefix + '_location');
        const provSelect = document.getElementById(prefix + '_addr_province');
        const citySelect = document.getElementById(prefix + '_addr_city');
        const brgySelect = document.getElementById(prefix + '_addr_barangay');
        const customBrgyInput = document.getElementById(prefix + '_addr_custom_barangay');
        const streetInput = document.getElementById(prefix + '_addr_street');
        const manualInput = document.getElementById(prefix + '_addr_manual_input');
        const previewText = document.getElementById(prefix + '_addr_preview_text');

        if (!hiddenLocation) return;
        hiddenLocation.value = fullAddress || '';
        if (previewText) previewText.textContent = fullAddress || 'Please select City and Barangay';

        if (!fullAddress || fullAddress.trim() === '') {
            if (provSelect) {
                provSelect.value = 'Metro Manila (NCR)';
                onPhProvinceChange(prefix);
            }
            return;
        }

        // Try intelligent parsing
        let matched = false;
        const addrLower = fullAddress.toLowerCase();

        for (const [prov, cities] of Object.entries(PH_LOCATIONS_DATA)) {
            for (const [city, brgys] of Object.entries(cities)) {
                if (addrLower.includes(city.toLowerCase())) {
                    if (provSelect) provSelect.value = prov;
                    onPhProvinceChange(prefix);
                    if (citySelect) citySelect.value = city;
                    onPhCityChange(prefix);

                    // Find matching barangay
                    let foundBrgy = '';
                    for (const b of brgys) {
                        if (addrLower.includes(b.toLowerCase())) {
                            foundBrgy = b;
                            break;
                        }
                    }

                    if (foundBrgy && brgySelect) {
                        brgySelect.value = foundBrgy;
                        onPhBarangayChange(prefix);
                    }

                    // Extract street part if possible
                    if (streetInput) {
                        let streetText = fullAddress;
                        streetText = streetText.replace(new RegExp(city, 'gi'), '');
                        streetText = streetText.replace(new RegExp(prov, 'gi'), '');
                        if (foundBrgy) streetText = streetText.replace(new RegExp('(?:Brgy\\.?\\s*)?' + foundBrgy, 'gi'), '');
                        streetText = streetText.replace(/^[, \t]+|[, \t]+$/g, '').replace(/,\s*,/g, ',');
                        streetInput.value = streetText;
                    }

                    matched = true;
                    break;
                }
            }
            if (matched) break;
        }

        if (!matched) {
            // If could not parse into known city, activate manual text mode so existing string is preserved
            const dropdownsWrap = document.getElementById('phAddrDropdownsWrap_' + prefix);
            const manualWrap = document.getElementById('phAddrManualWrap_' + prefix);
            const toggleBtn = document.getElementById('btnToggleManual_' + prefix);

            if (dropdownsWrap && manualWrap) {
                dropdownsWrap.style.display = 'none';
                manualWrap.style.display = 'block';
                if (toggleBtn) toggleBtn.innerHTML = '📋 Switch to Dropdowns';
                if (manualInput) manualInput.value = fullAddress;
            }
        }
    }
    </script>
@endif
