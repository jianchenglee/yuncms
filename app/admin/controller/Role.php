<?php
// +----------------------------------------------------------------------
// | YunCMS
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://www.yunalading.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: jabber <2898117012@qq.com>
// +----------------------------------------------------------------------


namespace app\admin\controller;

use app\admin\model\RoleModel;
use app\admin\validate\RoleValidate;
use think\exception\PDOException;

/**
 * Class Role
 * @package app\admin\controller
 */
class Role extends AdminBaseController {
    /**
     * 角色列表
     * @return \think\response\View
     */
    public function index() {
        $roleModel = new RoleModel();
        $list = $roleModel->paginate();
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        return view();
    }

    /**
     * 添加或修改角色
     * @return \think\response\View
     */
    public function update() {
        $roleModel = new RoleModel();
        if ($this->request->isPost()) {
            //验证数据
            $role_data = array_filter($this->post['role']);
            $roleValidate = new RoleValidate();
            if (!$roleValidate->check($role_data, [], 'update')) {
                $this->error($roleValidate->getError());
            }
            try {
                //添加或更新角色
                $role = $roleModel->saveAll([$role_data])[0];
                if (!empty($this->post['ac'])) {
                    //添加或更新权限
                    $access = array_filter($this->post['ac']);
                    $role->updateAccess($access);
                }
                $this->success('操作成功!', url('/admin/role'));
            } catch (PDOException $e) {
                //角色名已存在
                if ($e->getCode() == 10501) {
                    $this->error('角色已存在，操作失败');
                }
            }
        } else {
            if (!empty($this->param) && $this->param['id']) {
                //编辑页面初始化数据
                $role = RoleModel::get($this->param['id']);
                $this->assign('role', $role);
                $this->assign('access', json_encode($role->access()->select()));
            }
        }
        $this->assign('actions', config('authorization.menus'));
        return view();
    }

    /**
     * 软删除
     */
    public function remove() {
        if (!empty($this->param) && $this->param['id']) {
            try {
                if (RoleModel::destroy($this->param['id'])) {
                    $this->success('删除成功!');
                } else {
                    $this->error('删除失败!');
                }
            } catch (PDOException $e) {
                $this->error($e->getMessage());
            }
        } else {
            $this->error('参数错误!');
        }
    }

    /**
     * 回收站
     * @return \think\response\View
     */
    public function trash() {

        return view();
    }

    /**
     * 硬删除
     */
    public function destroy() {

    }

    /**
     * 清空回收站
     */
    public function emptyTrash() {

    }
}
