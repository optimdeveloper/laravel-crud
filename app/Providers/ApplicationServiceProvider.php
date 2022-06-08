<?php


namespace App\Providers;

use App\Repositories\Base\BaseRepository;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\CityRepository;
use App\Repositories\CityRepositoryInterface;
use App\Repositories\ConectedAppRepository;
use App\Repositories\ConectedAppRepositoryInterface;
use App\Repositories\CountryRepository;
use App\Repositories\CountryRepositoryInterface;
use App\Repositories\EventRepository;
use App\Repositories\EventRepositoryInterface;
use App\Repositories\FilterRepository;
use App\Repositories\FilterRepositoryInterface;
use App\Repositories\FilterToEventRepository;
use App\Repositories\FilterToEventRepositoryInterface;
use App\Repositories\GuestRepository;
use App\Repositories\GuestRepositoryInterface;
use App\Repositories\LogRepository;
use App\Repositories\LogRepositoryInterface;
use App\Repositories\MatchsRepository;
use App\Repositories\MatchsRepositoryInterface;
use App\Repositories\PassionRepository;
use App\Repositories\PassionRepositoryInterface;
use App\Repositories\PersonalFilterRepository;
use App\Repositories\PersonalFilterRepositoryInterface;
use App\Repositories\PersonalFilterToFilterRepository;
use App\Repositories\PersonalFilterToFilterRepositoryInterface;
use App\Repositories\PhotoEventRepository;
use App\Repositories\PhotoEventRepositoryInterface;
use App\Repositories\PromoteEventRepository;
use App\Repositories\PromoteEventRepositoryInterface;
use App\Repositories\ReportRepository;
use App\Repositories\ReportRepositoryInterface;
use App\Repositories\StateRepository;
use App\Repositories\StateRepositoryInterface;
use App\Repositories\SubscriptionRepository;
use App\Repositories\SubscriptionRepositoryInterface;
use App\Repositories\UserContactRepository;
use App\Repositories\UserContactRepositoryInterface;
use App\Repositories\UserToPassionRepository;
use App\Repositories\UserToPassionRepositoryInterface;
use App\Repositories\UserPhotoRepository;
use App\Repositories\UserPhotoRepositoryInterface;
use App\Repositories\UserProfileRepository;
use App\Repositories\UserProfileRepositoryInteface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\ValidationCodeRepository;
use App\Repositories\ValidationCodeRepositoryInterface;
use App\Services\LogService;
use App\Services\LogServiceInterface;
use Illuminate\Support\ServiceProvider;

class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //Repositories
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);

        $this->app->bind(CityRepositoryInterface::class, CityRepository::class);
        $this->app->bind(ConectedAppRepositoryInterface::class, ConectedAppRepository::class);
        $this->app->bind(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(FilterRepositoryInterface::class, FilterRepository::class);
        $this->app->bind(FilterToEventRepositoryInterface::class, FilterToEventRepository::class);
        $this->app->bind(GuestRepositoryInterface::class, GuestRepository::class);
        $this->app->bind(LogRepositoryInterface::class, LogRepository::class);
        $this->app->bind(MatchsRepositoryInterface::class, MatchsRepository::class);
        $this->app->bind(PassionRepositoryInterface::class, PassionRepository::class);
        $this->app->bind(PersonalFilterRepositoryInterface::class, PersonalFilterRepository::class);
        $this->app->bind(PersonalFilterToFilterRepositoryInterface::class, PersonalFilterToFilterRepository::class);
        $this->app->bind(PhotoEventRepositoryInterface::class, PhotoEventRepository::class);
        $this->app->bind(PromoteEventRepositoryInterface::class, PromoteEventRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, ReportRepository::class);
        $this->app->bind(StateRepositoryInterface::class, StateRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(UserContactRepositoryInterface::class, UserContactRepository::class);
        $this->app->bind(UserToPassionRepositoryInterface::class, UserToPassionRepository::class);
        $this->app->bind(UserPhotoRepositoryInterface::class, UserPhotoRepository::class);
        $this->app->bind(UserProfileRepositoryInteface::class, UserProfileRepository::class);
        $this->app->bind(ValidationCodeRepositoryInterface::class, ValidationCodeRepository::class);

        //Services
        $this->app->bind(LogServiceInterface::class, LogService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
