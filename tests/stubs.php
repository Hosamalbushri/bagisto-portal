<?php

namespace {
    use Webkul\Core\Core;

    if (! function_exists('core')) {
        function core()
        {
            return app(Core::class);
        }
    }

    if (! function_exists('themes')) {
        function themes()
        {
            static $activeTheme = null;

            return new class($activeTheme)
            {
                protected static $currentThemeCode = 'default';

                public function exists($theme)
                {
                    return true;
                }

                public function set($theme)
                {
                    self::$currentThemeCode = $theme;

                    return $this;
                }

                public function get()
                {
                    return self::$currentThemeCode;
                }

                public function find($code)
                {
                    return $this->current();
                }

                public function current()
                {
                    return new class(self::$currentThemeCode)
                    {
                        public $code;

                        public $viewsPath = null;

                        public $parent = null;

                        public function __construct($code)
                        {
                            $this->code = $code;
                        }

                        public function getViewPaths()
                        {
                            return [];
                        }
                    };
                }
            };
        }
    }
    if (! function_exists('bouncer')) {
        function bouncer()
        {
            return new class
            {
                public static bool $allowsAll = false;

                public function hasPermission($permission)
                {
                    return self::$allowsAll || (auth()->guard('admin')->check() ?? false);
                }
            };
        }
    }
}

namespace Webkul\Core {
    if (! class_exists(Core::class)) {
        class Core
        {
            public function getCurrentChannel()
            {
                return (object) [
                    'id' => 1,
                    'code' => 'default',
                    'name' => 'Default Store',
                    'theme' => 'default',
                    'logo_url' => null,
                    'favicon_url' => null,
                    'locales' => collect([(object) ['code' => 'en', 'name' => 'English']]),
                    'currencies' => collect([(object) ['code' => 'USD']]),
                    'default_locale' => (object) ['code' => 'en'],
                    'base_currency' => (object) ['code' => 'USD'],
                ];
            }

            public function getAllChannels()
            {
                return collect([$this->getCurrentChannel()]);
            }

            public function getCurrentLocale()
            {
                return (object) ['code' => 'en', 'direction' => 'ltr'];
            }

            public function getConfigData(string $field)
            {
                return null;
            }

            public function getSpeculationRules()
            {
                return [];
            }

            public function getRequestedLocaleCode(string $param = 'locale', bool $fallback = true)
            {
                return 'en';
            }

            public function getCurrentCurrency()
            {
                return new class
                {
                    public $code = 'USD';

                    public function toJson()
                    {
                        return json_encode(['code' => 'USD']);
                    }
                };
            }

            public function setCurrentCurrency(string $code)
            {
                //
            }
        }
    }
}

namespace Webkul\Core\Http\Middleware {
    if (! class_exists(SecureHeaders::class)) {
        class SecureHeaders
        {
            public const FRAMABLE = 'framable';
        }
    }

    if (! class_exists(PreventRequestsDuringMaintenance::class)) {
        class PreventRequestsDuringMaintenance
        {
            public function handle($request, $next)
            {
                return $next($request);
            }
        }
    }
}

namespace Webkul\Core\Eloquent {
    use Illuminate\Database\Eloquent\Model;

    if (! class_exists(TranslatableModel::class)) {
        class TranslatableModel extends Model
        {
            public function translations()
            {
                $translationClass = static::class.'Translation';

                if (class_exists($translationClass)) {
                    return $this->hasMany($translationClass);
                }

                return $this->hasMany(static::class);
            }

            public function translate($locale = null, $withFallback = false)
            {
                $locale = $locale ?? app()->getLocale();

                return $this->translations->firstWhere('locale', $locale);
            }

            public function getAttribute($key)
            {
                if (in_array($key, $this->translatedAttributes ?? [])) {
                    $translation = $this->translate();

                    if ($translation && isset($translation->$key)) {
                        return $translation->$key;
                    }
                }

                return parent::getAttribute($key);
            }

            public function translateOrNew($locale)
            {
                return new class
                {
                    public $draft_options = null;
                };
            }
        }
    }

    if (! class_exists(Repository::class)) {
        abstract class Repository
        {
            protected $model;

            public function __construct()
            {
                $this->makeModel();
            }

            public function makeModel()
            {
                $class = $this->model();

                if (app()->bound($class)) {
                    $this->model = app($class);
                } elseif (interface_exists($class)) {
                    $modelClass = str_replace('\\Contracts\\', '\\Models\\', $class);
                    $this->model = class_exists($modelClass) ? new $modelClass : null;
                } elseif (class_exists($class)) {
                    $this->model = new $class;
                } else {
                    $this->model = null;
                }

                return $this->model;
            }

            public function getModel()
            {
                return $this->model;
            }

            public function create(array $attributes)
            {
                return $this->model->create($attributes);
            }

            public function update(array $attributes, $id)
            {
                $record = $this->find($id);
                if ($record) {
                    $record->update($attributes);
                }

                return $record;
            }

            public function delete($id)
            {
                $record = $this->find($id);

                return $record ? $record->delete() : false;
            }

            public function find($id, $columns = ['*'])
            {
                return $this->model->find($id, $columns);
            }

            public function findOrFail($id, $columns = ['*'])
            {
                return $this->model->findOrFail($id, $columns);
            }

            public function findWhere(array $where, $columns = ['*'])
            {
                return $this->model->where($where)->get($columns);
            }

            public function findOneWhere(array $where, $columns = ['*'])
            {
                return $this->model->where($where)->first($columns);
            }

            abstract public function model();
        }
    }
}

namespace Webkul\Core\Repositories {
    if (! class_exists(CoreConfigRepository::class)) {
        class CoreConfigRepository
        {
            public function getSettingsValue(string $code, $channel = null, $locale = null)
            {
                return null;
            }
        }
    }

    if (! class_exists(ChannelRepository::class)) {
        class ChannelRepository {}
    }

    if (! class_exists(LocaleRepository::class)) {
        class LocaleRepository {}
    }

    if (! class_exists(CurrencyRepository::class)) {
        class CurrencyRepository {}
    }

    if (! class_exists(SubscribersListRepository::class)) {
        class SubscribersListRepository
        {
            protected static array $subscribers = [];

            public function findOneWhere(array $where)
            {
                foreach (self::$subscribers as $subscriber) {
                    $match = true;
                    foreach ($where as $key => $value) {
                        if (($subscriber->$key ?? null) != $value) {
                            $match = false;
                            break;
                        }
                    }
                    if ($match) {
                        return $subscriber;
                    }
                }

                return null;
            }

            public function findOneByField(string $field, mixed $value)
            {
                return $this->findOneWhere([$field => $value]);
            }

            public function create(array $data)
            {
                $id = count(self::$subscribers) + 1;
                $obj = new class(array_merge(['id' => $id], $data))
                {
                    public $id;

                    public $email;

                    public $token;

                    public $is_subscribed;

                    public $channel_id;

                    public function __construct(array $attributes)
                    {
                        foreach ($attributes as $k => $v) {
                            $this->$k = $v;
                        }
                    }

                    public function update(array $attributes)
                    {
                        foreach ($attributes as $k => $v) {
                            $this->$k = $v;
                        }

                        return true;
                    }
                };

                self::$subscribers[$id] = $obj;

                return $obj;
            }

            public function deleteWhere(array $where)
            {
                foreach (self::$subscribers as $id => $subscriber) {
                    $match = true;
                    foreach ($where as $key => $value) {
                        if (($subscriber->$key ?? null) != $value) {
                            $match = false;
                            break;
                        }
                    }
                    if ($match) {
                        unset(self::$subscribers[$id]);
                    }
                }
            }

            public function delete($id)
            {
                unset(self::$subscribers[$id]);
            }
        }
    }

    if (! class_exists(SubscribersListRepository::class)) {
        class SubscribersListRepository
        {
            protected static array $subscribers = [];

            public function findOneWhere(array $where)
            {
                foreach (self::$subscribers as $subscriber) {
                    $match = true;
                    foreach ($where as $key => $value) {
                        if (($subscriber->$key ?? null) != $value) {
                            $match = false;
                            break;
                        }
                    }
                    if ($match) {
                        return $subscriber;
                    }
                }

                return null;
            }

            public function findOneByField(string $field, mixed $value)
            {
                return $this->findOneWhere([$field => $value]);
            }

            public function create(array $data)
            {
                $id = count(self::$subscribers) + 1;
                $obj = new class(array_merge(['id' => $id], $data))
                {
                    public $id;

                    public $email;

                    public $token;

                    public $is_subscribed;

                    public $channel_id;

                    public function __construct(array $attributes)
                    {
                        foreach ($attributes as $k => $v) {
                            $this->$k = $v;
                        }
                    }

                    public function update(array $attributes)
                    {
                        foreach ($attributes as $k => $v) {
                            $this->$k = $v;
                        }

                        return true;
                    }
                };

                self::$subscribers[$id] = $obj;

                return $obj;
            }

            public function deleteWhere(array $where)
            {
                foreach (self::$subscribers as $id => $subscriber) {
                    $match = true;
                    foreach ($where as $key => $value) {
                        if (($subscriber->$key ?? null) != $value) {
                            $match = false;
                            break;
                        }
                    }
                    if ($match) {
                        unset(self::$subscribers[$id]);
                    }
                }
            }

            public function delete($id)
            {
                unset(self::$subscribers[$id]);
            }
        }
    }
}

namespace Webkul\CMS\Repositories {
    if (! class_exists(PageRepository::class)) {
        class PageRepository
        {
            public function findByUrlKey(string $urlKey)
            {
                return null;
            }

            public function find(int $id)
            {
                return null;
            }
        }
    }
}

namespace Webkul\Theme\Repositories {
    if (! class_exists(SectionRepository::class)) {
        class SectionRepository
        {
            public function findAllOfType($type, $channelId = null, $theme = null)
            {
                return collect();
            }
        }
    }
}

namespace Webkul\Core\Models {
    use Illuminate\Database\Eloquent\Model;

    if (! class_exists(ChannelProxy::class)) {
        class ChannelProxy extends Model
        {
            public static function modelClass()
            {
                return Model::class;
            }
        }
    }
}

namespace Webkul\Theme\Enums {
    if (! enum_exists(SectionTypeEnum::class)) {
        enum SectionTypeEnum: string
        {
            case IMAGE_CAROUSEL = 'image_carousel';
            case STATIC_CONTENT = 'static_content';
            case SERVICES_CONTENT = 'services_content';
            case FOOTER_LINKS = 'footer_links';
        }
    }
}

namespace Webkul\DataGrid {
    if (! class_exists(DataGrid::class)) {
        abstract class DataGrid
        {
            abstract public function prepareColumns();

            abstract public function prepareActions();
        }
    }
}
