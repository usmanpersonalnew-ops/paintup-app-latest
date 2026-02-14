
# SUPERVISOR APP V4 - MASTER DEVELOPMENT BLUEPRINT

## 1. PROJECT CONTEXT
**Goal:** Build a "Field Quotation System" (Supervisor App) for a painting company.
**Stack:** Laravel (Backend) + Vue.js/Inertia (Frontend) + Tailwind CSS.
**User:** Field Supervisor (Mobile/Tablet User).
**Constraint:** UI must match the V4 Wireframes exactly.

---

## 2. DATABASE & MODELS (FOUNDATION)
Before building Views, ensure the Database has these exact columns.

### A. Table: `project_zones` (The Rooms)
- `project_id` (FK)
- `name` (String)
- `type` (Enum: 'Interior', 'Exterior')
- `length`, `breadth`, `height` (Decimal, Nullable) - *Used for Default Calculations*

### B. Table: `quote_items` (Paint Jobs)
- `project_zone_id` (FK) - *Standardized Name*
- `surface_id`, `product_id`, `system_id` (FKs)
- `measurement_mode` (Enum: 'DEFAULT', 'MANUAL')
- `manual_length`, `manual_breadth` (Decimal)
- `deductions` (Decimal) - *Critical for Screen C*
- `pricing_mode` (Enum: 'CALCULATED', 'LUMPSUM')
- `manual_price` (Decimal)
- `calculated_qty`, `final_amount` (Decimal)

### C. Table: `quote_services` (Add-ons & Repairs)
- `project_zone_id` (FK)
- `master_service_id` (FK, Nullable for Custom)
- `custom_name` (String)
- `unit_type` (Enum: 'AREA', 'LINEAR', 'COUNT')
- `length`, `breadth`, `count` (Decimal) - *Dynamic Inputs*
- `rate`, `final_amount` (Decimal)
- `photo` (String/Path) - *Mandatory for Repairs*

### D. Table: `quotes` (The Transaction)
- `project_id` (FK)
- `discount_amount` (Decimal) - *Global Flat Discount*
- `tax_percent` (Decimal) - *e.g., 18%*
- `grand_total` (Decimal)

---

## 3. ROUTES (`routes/web.php`)
Register these exact routes under the `supervisor` middleware group.

```php
// Screen A: Create Zone
Route::post('/projects/{project}/zones', [ZoneController::class, 'store'])->name('supervisor.zones.store');

// Screen B: Dashboard
Route::get('/zones/{zone}', [ZoneDashboardController::class, 'index'])->name('supervisor.zones.show');

// Screen C: Paint Items
Route::get('/zones/{zone}/paint', [QuoteItemController::class, 'create'])->name('supervisor.zones.paint.create');
Route::post('/zones/{zone}/paint', [QuoteItemController::class, 'store'])->name('supervisor.zones.paint.store');

// Screen D: Service/Repair
Route::get('/zones/{zone}/service', [ServiceController::class, 'create'])->name('supervisor.zones.service.create');
Route::post('/zones/{zone}/service', [ServiceController::class, 'store'])->name('supervisor.zones.service.store');

// Screen E: Summary
Route::get('/projects/{project}/summary', [SummaryController::class, 'show'])->name('supervisor.summary');
Route::post('/projects/{project}/finalize', [SummaryController::class, 'finalize'])->name('supervisor.finalize');

```

---

## 4. UI WIREFRAMES & LOGIC (SCREENS A - E)

### SCREEN A: ZONE CREATION

**Goal:** Capture Room Dimensions for auto-calculation later.

```text
┌───────────────────────────────────────┐
│  < Back to Project   CREATE NEW ZONE  │
├───────────────────────────────────────┤
│  1. ZONE NAME                         │
│  [ e.g. Master Bedroom              ] │
│                                       │
│  2. ZONE TYPE                         │
│  (•) Interior        ( ) Exterior     │
│                                       │
│  3. DEFAULT DIMENSIONS (in Feet)      │
│  Length: [ 12 ]                       │
│  Breadth: [ 10 ]                      │
│  Height: [ 10 ]                       │
│                                       │
│  [ CREATE ZONE ]                      │
└───────────────────────────────────────┘

```

---

### SCREEN B: ZONE DASHBOARD (The Hub)

**Goal:** Overview of the room's costs and photos.

```text
┌───────────────────────────────────────┐
│  < Back         ZONE: MASTER BEDROOM  │
│  Dim: 12 x 10 x 10 ft                 │
├───────────────────────────────────────┤
│  [ SECTION 1: PHOTOS ]                │
│  [+] Add Site Photo (Min 5 req)       │
│  [Img1] [Img2] [Img3] ...             │
│                                       │
│  [ SECTION 2: ESTIMATE ]              │
│  1. Interior Wall (Royale)  ₹ 26,000  │
│  2. Ceiling (Tractor)       ₹  8,500  │
│  3. Crack Filling (Repair)  ₹    750  │
│                                       │
│  [ + Add Paint Item     ] (-> Scrn C) │
│  [ + Add Service/Repair ] (-> Scrn D) │
│                                       │
│  [ Duplicate Zone ]      [ Done ]     │
└───────────────────────────────────────┘

```

---

### SCREEN C: ADD PAINT ITEM (The Complex Calculator)

**Logic:** `Net Area = (Gross Area - Deductions)`. `Price = Net Area * Rate`.

```text
┌───────────────────────────────────────────────┐
│  < Back          ADD PAINT ITEM               │
├───────────────────────────────────────────────┤
│  1. SURFACE SELECTION                         │
│  [ Interior Wall                       ▼ ]    │
│                                               │
│  2. PRODUCT FILTER (Toggle)                   │
│  [ ALL ] [ ECONOMY ] [ PREMIUM ] [ LUXURY ]   │
│  *Filters the dropdown below based on Tier* │
│                                               │
│  3. PRODUCT                                   │
│  [ Royale Glitz (Luxury)               ▼ ]    │
│                                               │
│  4. SYSTEM                                    │
│  [ Fresh Painting (₹ 65/sqft)          ▼ ]    │
│  Desc: "3 Coats with Teflon support"          │
│                                               │
│  5. MEASUREMENTS                              │
│  Mode: [•] Use Room Default   [ ] Manual      │
│                                               │
│  -- IF DEFAULT SELECTED --                    │
│  Ref: 12 x 10 x 10 ft                         │
│  Gross Area: 440 sqft (Auto-calc)             │
│                                               │
│  -- IF MANUAL SELECTED --                     │
│  Length: [    ]   Height: [    ]              │
│  Gross Area: [ Calculated ]                   │
│                                               │
│  Deductions (sqft): [ 42 ]                    │
│  -------------------------------------------  │
│  NET QUANTITY: 398 SQFT                       │
│                                               │
│  6. PRICING                                   │
│  Mode: [•] Calculated    [ ] Lumpsum          │
│                                               │
│  -- IF CALCULATED --                          │
│  398 sqft x ₹ 65 = ₹ 25,870                   │
│                                               │
│  -- IF LUMPSUM --                             │
│  Manual Price: [ ₹          ]                 │
│                                               │
│  [ SAVE ITEM ]                                │
└───────────────────────────────────────────────┘

```

---

### SCREEN D: ADD SERVICE / REPAIR

**Logic:** Dynamic inputs based on `unit_type`. Mandatory photo for repairs.

```text
┌───────────────────────────────────────┐
│  < Back        ADD SERVICE / REPAIR   │
├───────────────────────────────────────┤
│  MODE: [•] Catalog     [ ] Custom     │
│                                       │
│  1. SELECT SERVICE                    │
│  [ Crack Filling (₹ 15/rft)    ▼ ]    │
│  *System detects Unit: LINEAR* │
│  *System detects Is Repair: YES* │
│                                       │
│  2. MEASUREMENTS (Dynamic UI)         │
│                                       │
│  -- IF UNIT == 'AREA' --              │
│  Length: [    ]   Height: [    ]      │
│                                       │
│  -- IF UNIT == 'LINEAR' --            │
│  Running Feet: [ 50 ]                 │
│                                       │
│  -- IF UNIT == 'COUNT' --             │
│  Quantity (Nos): [    ]               │
│                                       │
│  3. EVIDENCE (Conditional)            │
│  [!] Mandatory for Repairs            │
│  [ 📸 Take Photo / Upload ]           │
│                                       │
│  4. TOTAL                             │
│  50 Rft x ₹ 15 = ₹ 750                │
│                                       │
│  [ SAVE SERVICE ]                     │
└───────────────────────────────────────┘

```

---

### SCREEN E: QUOTE SUMMARY (The Close)

**Goal:** Final totals, discounts, and PDF generation.

```text
┌───────────────────────────────────────┐
│  < Back         QUOTE SUMMARY         │
├───────────────────────────────────────┤
│  ZONE 1: MASTER BEDROOM               │
│  Paint Items: ............ ₹ 25,870   │
│  Services: ............... ₹    750   │
│  ----------------------------------   │
│  ZONE 2: EXTERIOR WALLS               │
│  Paint Items: ............ ₹ 80,000   │
│  ----------------------------------   │
│  SUBTOTAL: ............... ₹ 1,06,620 │
│                                       │
│  GLOBAL DISCOUNT (Flat ₹):            │
│  [ - 6620            ]                │
│                                       │
│  TAX / GST (%):                       │
│  [ 18                ] %              │
│  Tax Amount: ₹ 18,000                 │
│                                       │
│  GRAND TOTAL: ............ ₹ 1,18,000 │
│                                       │
│  NOTES / EXCLUSIONS:                  │
│  [ Deep cleaning not included...    ] │
│                                       │
│  [ GENERATE PDF QUOTE ]               │
└───────────────────────────────────────┘

```

---

## 5. EXECUTION PLAN FOR AGENT

1. **Database Audit:** Check if `quotes`, `project_zones`, `quote_items`, `quote_services` have all the columns listed in Section 2. If not, create migrations.
2. **Route Registration:** Ensure `web.php` matches Section 3.
3. **View Implementation:** Build the Vue components for Screens A, C, D, and E following the wireframes exactly.
* **Screen C Focus:** Implement the Product Filter and Deduction Logic.
* **Screen D Focus:** Implement the Unit Type Switcher and Photo Validation.


4. **Testing:** Verify the flow from creating a zone -> adding paint -> adding service -> viewing summary.

```

```