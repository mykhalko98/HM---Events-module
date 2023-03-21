<?php

namespace Modules\Events\Forms\Admin;

use Kris\LaravelFormBuilder\Form;

//Models
use Hubmachine\Access\Models\UserRolePermissions;
use Hubmachine\Access\Models\UserPermissionTypes;
use Hubmachine\Access\Models\UserRoles;

class Settings extends Form
{
    public function buildForm()
    {
        $this->buildFormFields();

        $this->add('submit', 'submit', ['label' => 'Save', 'attr' => ['class' => 'btn btn-primary mt-2 mb-2']]);
    }

    /**
     * Build form fields
     */
    public function buildFormFields()
    {
        $values = $this->getSettingValues();

        $this->add('events_prefix', 'text', [
            'label'      => 'URL Prefix',
            'label_attr' => ['class' => 'control-label mt-3'],
            'wrapper'    => ['class' => 'form-group pb-2'],
            'rules'      => 'required',
            'attr'       => ['class' => 'form-control', 'placeholder' => '', 'oninput' => "$(this).val($(this).val().replace(/\s+/g, '-').replace(/[^a-z0-9-.]/g, '').toLowerCase())"],
            'value'      => $values['events_prefix']
        ]);

        foreach ($values['user_permissions'] as $user_permission) {
            $this->add('permissions['.$user_permission['action'].']', 'select', [
                'label'         => $user_permission['label'],
                'label_attr'    => ['class' => 'control-label mt-3'],
                'wrapper'       => ['class' => 'form-group border-top'],
                'choices'       => ['everyone' => 'Everyone', 'admin' => 'Admin only', 'by_user_role' => 'By user role'],
                'selected'      => isset($user_permission['value']) ? $user_permission['value'] : null,
                'default_value' => 'everyone',
                'rules'         => 'required',
                'attr'          => ['class' => 'selectpicker form-control', 'onchange' => 'window.hm.admin.events.settings.toggleSelector($(this))'],
            ]);

            $this->add('permissions_by_role['.$user_permission['action'].']', 'select', [
                'label'    => $user_permission['label'].' By Role',
                'label_attr' => ['class' => 'd-none'],
                'choices'  => UserRoles::all()->pluck('name', 'id')->toArray(),
                'selected' => isset($user_permission['by_role']) ? $user_permission['by_role'] : [],
                'wrapper'  => ['class' => 'form-group pb-2', 'id' => 'visibility_multichoices', 'style' => !isset($user_permission['by_role']) ? 'display: none' : ''],
                'class'    => 'form-group-checkbox',
                'choice_options' => [
                    'wrapper'    => ['class' => 'choice-wrapper ml-3'],
                    'label_attr' => ['class' => 'label-class mb-0'],
                ],
                'attr' => [
                    'multiple'         => TRUE,
                    'data-actions-box' => 'true',
                    'class'            => 'selectpicker hide-parent-group fselectpicker form-control'
                ]
            ]);
        }
    }

    /**
     *   populate form
     *
     * @param array $values
     */
    public function populate($values = [])
    {
        $fields = $this->getFields();

        foreach ($values as $field => $value) {
            if (isset($fields[$field])) {
                $fields[$field]->setValue($value);
            }
        }
    }


    /**
     * Get setting values.
     *
     * @return array
     */
    private function getSettingValues()
    {
        $events_prefix = settings()->get('hubmachine.events.prefix');

        $user_permissions = [];
        $user_permission_types = UserPermissionTypes::where('type', 'LIKE', 'events')->get();
        foreach ($user_permission_types as $user_permission_type) {
            $user_permissions['permission_'.$user_permission_type->action] = $this->getPermissions($user_permission_type);
        }

        return [
            'events_prefix' => $events_prefix,
            'user_permissions' => $user_permissions
        ];
    }

    private function getPermissions($user_permission_type)
    {
        $user_roles = UserRoles::all()->pluck('role', 'id')->toArray();
        $user_role_permissions = UserRolePermissions::select('user_role_permissions.*', 'user_roles.role')
            ->leftJoin('user_roles', 'user_roles.id', '=', 'user_role_permissions.role_id')
            ->where('user_role_permissions.permission_id', '=', $user_permission_type->getKey())
            ->where('user_role_permissions.value', '=', 1)
            ->get()
            ->toArray();

        if (count($user_roles) === count($user_role_permissions)) {
            return ['action' => $user_permission_type->action, 'label' => $user_permission_type->description, 'value' => 'everyone', 'by_role' => null];
        }

        $admin_roles = UserRoles::whereIn('role', ['superadmin', 'admin'])->pluck('id', 'role')->toArray();
        $only_admin = true;
        foreach ($user_role_permissions as $user_role_permission) {
            if (!in_array($user_role_permission['role_id'], $admin_roles)) {
                $only_admin = false;
            }
        }
        if (count($admin_roles) === count($user_role_permissions) && $only_admin) {
            return ['action' => $user_permission_type->action, 'label' => $user_permission_type->description, 'value' => 'admin', 'by_role' => null];
        }

        return ['action' => $user_permission_type->action, 'label' => $user_permission_type->description, 'value' => 'by_user_role', 'by_role' => array_column($user_role_permissions, 'role_id')];
    }
}