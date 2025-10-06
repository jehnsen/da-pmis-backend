# PMIS Backend - Quick Reference Card

## 🎯 What Was Built For You

### Created Files: 36

**Controllers (8):**
- ProjectController
- ProgressReportController
- CropProductionController
- LivestockStatisticController
- NewsUpdateController
- DocumentController
- ContactInquiryController
- NewsletterSubscriptionController

**Requests (16):**
- All Store/Update request classes for the 8 modules above

**Resources (12):**
- All API response resource classes with RBAC logic

**Providers (9):**
- Service providers created (need bindings configured)

---

## ⚡ Quick Copy-Paste Templates

### Create a Repository Interface

```php
<?php
namespace App\Interfaces;

interface YourModelRepositoryInterface
{
    public function all(array $filters = []);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function paginate(int $perPage = 15, array $filters = []);
}
```

### Create a Repository

```php
<?php
namespace App\Repositories;

use App\Models\YourModel;
use App\Interfaces\YourModelRepositoryInterface;

class YourModelRepository implements YourModelRepositoryInterface
{
    public function __construct(protected YourModel $model) {}

    public function all(array $filters = [])
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        return $this->model->paginate($perPage);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    public function delete($id)
    {
        $record = $this->find($id);
        if ($record) {
            $record->delete();
            return $record;
        }
        return null;
    }
}
```

### Create a Service

```php
<?php
namespace App\Services;

use App\Interfaces\YourModelRepositoryInterface;

class YourModelService
{
    public function __construct(
        private readonly YourModelRepositoryInterface $repo
    ) {}

    public function list(int $perPage = 15, array $filters = [])
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function getById(int $id)
    {
        return $this->repo->find($id);
    }

    public function update(int $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->repo->delete($id);
    }
}
```

### Create a Model

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class YourModel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'field1',
        'field2',
        // ... add all fillable fields
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
        'date_field' => 'date',
    ];

    // Define relationships
    public function relatedModel(): BelongsTo
    {
        return $this->belongsTo(RelatedModel::class);
    }
}
```

### Create a Migration

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_name', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('related_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->date('date_field');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('related_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_name');
    }
};
```

### Update Service Provider

```php
<?php
namespace App\Providers;

use App\Interfaces\YourModelRepositoryInterface;
use App\Repositories\YourModelRepository;
use Illuminate\Support\ServiceProvider;

class YourModelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            YourModelRepositoryInterface::class,
            YourModelRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
```

### Add Routes

```php
// In routes/api.php

use App\Http\Controllers\YourModelController;

// Public routes
Route::get('your-models', [YourModelController::class, 'index']);
Route::get('your-models/{id}', [YourModelController::class, 'show']);

// Protected routes (require authentication)
Route::middleware('auth:api')->group(function () {
    Route::post('your-models', [YourModelController::class, 'store']);
    Route::put('your-models/{id}', [YourModelController::class, 'update']);
    Route::delete('your-models/{id}', [YourModelController::class, 'destroy']);
});
```

---

## 🗂️ File Locations

| Component | Location | Count Needed |
|-----------|----------|--------------|
| Controllers | `app/Http/Controllers/` | ✅ 8 created |
| Requests | `app/Http/Requests/` | ✅ 16 created |
| Resources | `app/Http/Resources/` | ✅ 12 created |
| Interfaces | `app/Interfaces/` | ❌ 9 needed |
| Repositories | `app/Repositories/` | ❌ 9 needed |
| Services | `app/Services/` | ❌ 9 needed |
| Models | `app/Models/` | ❌ ~20 needed |
| Migrations | `database/migrations/` | ❌ ~20 needed |
| Providers | `app/Providers/` | ✅ 9 created |

---

## 📝 To-Do Checklist

### Data Layer
- [ ] Create 9 Repository Interfaces in `app/Interfaces/`
- [ ] Create 9 Repository Implementations in `app/Repositories/`
- [ ] Create 9 Service Classes in `app/Services/`
- [ ] Create ~20 Model classes in `app/Models/`
- [ ] Create ~20 Migration files in `database/migrations/`

### Configuration
- [ ] Update 9 Service Providers with bindings
- [ ] Register all providers in `bootstrap/providers.php`
- [ ] Add all routes to `routes/api.php`

### Cross-Cutting
- [ ] Create AuditLogger trait in `app/Traits/`
- [ ] Create audit logging middleware (optional)
- [ ] Configure RBAC permissions
- [ ] Create database seeders for testing

### Testing
- [ ] Run migrations: `php artisan migrate`
- [ ] Test all endpoints
- [ ] Verify RBAC logic
- [ ] Check audit logging

---

## 🚀 Commands You'll Need

```bash
# Create migration
php artisan make:migration create_projects_table

# Create model
php artisan make:model Project

# Create model with migration
php artisan make:model Project -m

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (drop all tables and re-run)
php artisan migrate:fresh

# Seed database
php artisan db:seed

# List all routes
php artisan route:list

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Create seeder
php artisan make:seeder ProjectsTableSeeder
```

---

## 🔍 What to Implement by Module

### Module 1: Projects
**Files to create:**
1. `app/Interfaces/ProjectRepositoryInterface.php`
2. `app/Repositories/ProjectRepository.php`
3. `app/Services/ProjectService.php`
4. `app/Models/Project.php`
5. `app/Models/ProjectType.php`
6. `app/Models/ProjectStatus.php`
7. `app/Models/ProjectTeamMember.php`
8. `app/Models/ProjectMilestone.php`
9. Migrations for all above tables
10. Update `ProjectServiceProvider` with binding
11. Add to `bootstrap/providers.php`
12. Add routes to `api.php`

**Repeat for each module:**
- ProgressReport
- CropProduction
- LivestockStatistic
- NewsUpdate
- Document
- ContactInquiry
- NewsletterSubscription
- AuditLog

---

## 🎯 Priority Order

1. **Start with Projects module** (most complex, good template)
2. **Then ProgressReport** (has nested metrics)
3. **Then simpler modules** (CropProduction, LivestockStatistic)
4. **Then content modules** (NewsUpdate, Document)
5. **Then engagement modules** (ContactInquiry, NewsletterSubscription)
6. **Finally audit logging** (observes all other modules)

---

## 💡 Pro Tips

1. ✅ **Already done:** Controllers, Requests, Resources
2. 🔄 **Copy existing pattern:** Use DepartmentController/Service/Repository as template
3. 📦 **Work in chunks:** Complete one module fully before moving to next
4. 🧪 **Test as you go:** Use Postman/curl to test each endpoint
5. 🗂️ **Organize imports:** Keep namespaces consistent
6. 📝 **Document as you build:** Add comments for complex logic

---

## 🎓 Resources

- **Completed Files:** Check `app/Http/Controllers/`, `app/Http/Requests/`, `app/Http/Resources/`
- **Example Pattern:** `app/Repositories/DepartmentRepository.php`
- **Full Guide:** [SETUP_GUIDE.md](SETUP_GUIDE.md)
- **Status:** [IMPLEMENTATION_STATUS.md](IMPLEMENTATION_STATUS.md)
- **Summary:** [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)

---

**Ready to code? Start with creating your first Repository Interface!** 🚀
