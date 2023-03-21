<?php

namespace Modules\Events\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Hubmachine\Access\Models\UserPermissionTypes;
use Hubmachine\Access\Models\UserRoles;
use Hubmachine\Access\Models\UserRolePermissions;
use Hubmachine\Menu\Models\Menu;
use Hubmachine\Core\Models\Layout\Layouts;

use Kris\LaravelFormBuilder\FormBuilder;

use App\Helpers\AddOn;
use Module;
use Route;

class IndexController extends Controller
{

    /**
     * @param Request $request
     * @param FormBuilder $formBuilder
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getFormSettings(Request $request, FormBuilder $formBuilder)
    {
        $module        = Module::find('events');
        $module_config = AddOn::getConfig($module->getPath());

        $form = $formBuilder->create($module_config['settings']['form'], $module_config['settings']['params']);

        $content = view('events::admin.settings', ['form' => $form, 'module_config' => $module_config])->render();
        return response()->json(['status' => true, 'content' => $content], 200, [], JSON_PRETTY_PRINT);
    }

    private function existEventsURI($new_uri)
    {
        $names_exists = ['events.events', 'events.tag.events', 'events.category.events'];
        $new_uris = [
            $new_uri,
            $new_uri . '/tag',
            $new_uri . '/category',
        ];
        $routes = Route::getRoutes()->getRoutes();
        foreach ($routes as $r) {
            if (in_array($r->uri, $new_uris) && !in_array($r->getName(), $names_exists)) {
                return true;
            }
        }
        return false;
    }

    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'events_prefix' => 'required|regex:/^[a-z0-9-.]+$/',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()->all()]);
        }

        DB::beginTransaction();
        try {
            $events_prefix = $request->get('events_prefix');
            if($events_prefix[0] == '/') {
                $events_prefix = substr($events_prefix, 1);
            }
            if($events_prefix[strlen($events_prefix)-1] == '/') {
                $events_prefix = substr($events_prefix, 0, -1);
            }
            $events_prefix_old = settings()->get('hubmachine.events.prefix');
            if ($events_prefix !== $events_prefix_old) {
                if ($this->existEventsURI($events_prefix) === true) {
                    return response()->json(['status' => false, 'errors' => ['URI - "' . $events_prefix . '" already taken.']]);
                }

                settings()->set('hubmachine.events.prefix', $events_prefix);

                $menu = Menu::where('name', '=', 'main-menu')->first();
                if ($menu) {
                    $menu_layout = Layouts::where('object_type', '=', 'menu')->where('object_id', '=', $menu->getKey())->orderBy('updated_at', 'DESC')->first();
                    if ($menu_layout) {
                        $data = json_decode($menu_layout->data, true);

                        foreach ($data as $key => $menu_item) {
                            if (strpos($menu_item['href'], '/' . $events_prefix_old) !== 0) {
                                continue;
                            }
                            $data[$key]['href'] = str_replace('/' . $events_prefix_old, '/' . $events_prefix, $menu_item['href']);
                        }
                        $menu_layout->data = json_encode($data);
                        $menu_layout->save();
                    }
                }
            }

            $set_user_permissions = false;
            $user_permissions = $request->get('permissions');
            $user_permissions_by_role = $request->get('permissions_by_role');
            foreach ($user_permissions as $action => $user_permission) {
                switch ($user_permission) {
                    case 'everyone':
                        $user_roles = UserRoles::all()->pluck('id')->toArray();
                        $set_user_permissions = $this->setUserPermissions($user_roles, $action);
                        break;
                    case 'admin':
                        $user_roles = UserRoles::whereIn('role', ['superadmin', 'admin'])->pluck('id')->toArray();
                        $set_user_permissions = $this->setUserPermissions($user_roles, $action);
                        break;
                    case 'by_user_role':
                        $permissions_by_role = !empty($user_permissions_by_role[$action]) ? $user_permissions_by_role[$action] : [];
                        $user_roles = explode(',', array_shift($permissions_by_role));
                        $set_user_permissions = $this->setUserPermissions($user_roles, $action);
                        break;
                    default:
                        return response()->json(['status' => false, 'errors' => ['Choose visibility type from form.']]);
                }
            }

            if (!$set_user_permissions) {
                return response()->json(['status' => false, 'errors' => ['Error saving.']]);
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
            DB::rollback();
            return response()->json(['status' => false, 'errors' => [$e->getMessage()]]);
        }
        DB::commit();

        return response()->json(['status' => true, 'message' => __('Settings successfully updated.')]);
    }

    private function setUserPermissions($user_roles, $action)
    {
        if (empty($user_roles) || empty($action)) {
            return false;
        }

        $permission_type = UserPermissionTypes::where('type', '=', 'events')->where('action', '=', $action)->first();
        $all_user_roles = UserRoles::all()->pluck('role', 'id');
        foreach ($all_user_roles as $role_id => $role) {
            UserRolePermissions::updateOrCreate(['role_id' => $role_id, 'permission_id' => $permission_type->getKey()], ['value' => in_array($role_id, $user_roles)]);
        }

        return true;
    }
}