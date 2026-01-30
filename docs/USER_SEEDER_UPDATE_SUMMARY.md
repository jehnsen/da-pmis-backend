# UserSeeder Update Summary

## Changes Made

The UserSeeder has been updated to align with the **RA 7160 Provincial LGU Governance Platform** approval workflow.

### Key Updates:

1. **Removed**: Regional Director user (deprecated role)
2. **Added**: Provincial Governor user (final approval authority)
3. **Added**: 4 Barangay Development Council Officers (project drafting/proposal entry point)
4. **Updated**: Municipal Officers → Municipal Planning Officers (MPDO)
5. **Updated**: Provincial Officers → Provincial Planning Officers (PPDO)
6. **Updated**: Role mappings to match RoleSeeder (Department Head → Sector Head, Field Officer/Agricultural Technician → Technical Officer)

---

## RA 7160 Approval Workflow

```
Level 0: Barangay Development Council (BDC) - Project drafting/proposal entry point
    ↓
Level 1: Municipal Planning & Development Office (MPDO) - Municipal validation
    ↓
Level 2: Provincial Planning & Development Office (PPDO) - Provincial technical review
    ↓
Level 3: Provincial Governor - Final approval authority
```

---

## Test User Credentials

**Default Password for ALL users:** `Password123!`

### System Administration

| Role | Name | Username | Email |
|------|------|----------|-------|
| System Administrator | Juan Dela Cruz | `admin` | admin@da-caraga.gov.ph |

### Approval Workflow Users

#### Level 3: Provincial Governor (Final Approval)
| Name | Username | Email |
|------|----------|-------|
| Maria Santos-Rodriguez | `governor_caraga` | governor@caraga.gov.ph |

#### Level 2: Provincial Planning Officers (PPDO)
| Name | Username | Province | Email |
|------|----------|----------|-------|
| Carlos Mendez-Silva | `ppdo_agusannorte` | Agusan del Norte | ppdo.agusannorte@caraga.gov.ph |
| Sofia Ramirez-Torres | `ppdo_agusansur` | Agusan del Sur | ppdo.agusansur@caraga.gov.ph |
| Benjamin Cruz-Flores | `ppdo_surigaonorte` | Surigao del Norte | ppdo.surigaonorte@caraga.gov.ph |

#### Level 1: Municipal Planning Officers (MPDO)
| Name | Username | Municipality | Email |
|------|----------|--------------|-------|
| Rafael Santos-Aquino | `mpdo_butuan` | Butuan City | mpdo.butuan@caraga.gov.ph |
| Isabella Garcia-Reyes | `mpdo_cabadbaran` | Cabadbaran City | mpdo.cabadbaran@caraga.gov.ph |
| Gabriel Fernandez-Lopez | `mpdo_surigao` | Surigao City | mpdo.surigao@caraga.gov.ph |

#### Level 0: Barangay Development Council Officers (BDC)
| Name | Username | Barangay | Email |
|------|----------|----------|-------|
| Diana Rodriguez-Castro | `bdc_bunawan` | Bunawan | bdc.bunawan@caraga.gov.ph |
| Marco Villa-Santos | `bdc_bayugan` | Bayugan | bdc.bayugan@caraga.gov.ph |
| Anna Marie Gonzales | `bdc_prosperidad` | Prosperidad | bdc.prosperidad@caraga.gov.ph |
| Ricardo Magpantay | `bdc_sanfrancisco` | San Francisco | bdc.sanfrancisco@caraga.gov.ph |

### Other Roles

#### LGU Sector Heads
| Name | Username | Department | Email |
|------|----------|------------|-------|
| Roberto Villanueva | `rvillanueva` | Rice Program | rvillanueva@da-caraga.gov.ph |
| Carmen Reyes-Lopez | `clopez` | High-Value Crops | clopez@da-caraga.gov.ph |
| Antonio Mendoza | `amendoza` | Livestock Development | amendoza@da-caraga.gov.ph |

#### Project Managers
| Name | Username | Department | Email |
|------|----------|------------|-------|
| Elena Garcia-Cruz | `ecruz` | Field Operations | ecruz@da-caraga.gov.ph |
| Ferdinand Aquino | `faquino` | Agricultural Engineering | faquino@da-caraga.gov.ph |
| Rosalinda Fernandez | `rfernandez` | Fisheries & Aquatic Resources | rfernandez@da-caraga.gov.ph |

#### Technical Officers
| Name | Username | Department | Email |
|------|----------|------------|-------|
| Jose Ramos | `jramos` | Agricultural Extension Services | jramos@da-caraga.gov.ph |
| Luisa Bautista | `lbautista` | Field Operations | lbautista@da-caraga.gov.ph |
| Miguel Torres | `mtorres` | Organic Agriculture | mtorres@da-caraga.gov.ph |

#### Data Encoders
| Name | Username | Department | Email |
|------|----------|------------|-------|
| Patricia Santos | `psantos` | Planning & Monitoring | psantos@da-caraga.gov.ph |
| Ricardo Castillo | `rcastillo` | Planning & Monitoring | rcastillo@da-caraga.gov.ph |

#### Public Access
| Name | Username | Email |
|------|----------|-------|
| Public User | `public` | public@example.com |

---

## Testing Workflow

### Test Scenario: Project Approval Workflow

1. **Login as BDC Officer** (`bdc_bunawan` / Password123!)
   - Create new project proposal
   - Submit for approval (goes to Municipal level)

2. **Login as MPDO** (`mpdo_butuan` / Password123!)
   - Review barangay-submitted project
   - Approve (goes to Provincial PPDO level)

3. **Login as PPDO** (`ppdo_agusannorte` / Password123!)
   - Conduct technical review of project
   - Approve (goes to Governor level)

4. **Login as Governor** (`governor_caraga` / Password123!)
   - Final approval/rejection
   - Project becomes active upon approval

---

## Database Refresh

To apply these changes:

```bash
# Fresh migration with all seeders
php artisan migrate:fresh --seed

# Or run UserSeeder only (if database already seeded)
php artisan db:seed --class=UserSeeder
```

---

**Updated:** 2026-01-30
**Status:** Ready for Testing
**Compliance:** RA 7160 (Local Government Code of 1991)
