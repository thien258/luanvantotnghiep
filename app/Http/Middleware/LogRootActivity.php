<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RootActivityLog;
use Symfony\Component\HttpFoundation\Response;

class LogRootActivity
{
    private array $actionMap = [
        'GET /admin'                                        => 'Xem dashboard',
        'GET /admin/user'                                   => 'Xem danh sach user',
        'GET /admin/user/create'                            => 'Mo form tao user moi',
        'POST /admin/user'                                  => 'Tao user moi',
        'GET /admin/user/{id}/edit'                         => 'Mo form sua user #{id}',
        'PUT /admin/user/{id}'                              => 'Cap nhat user #{id}',
        'PATCH /admin/user/{id}'                            => 'Cap nhat user #{id}',
        'DELETE /admin/user/{id}'                           => 'Xoa user #{id}',
        'GET /admin/product'                                => 'Xem danh sach san pham',
        'GET /admin/product/create'                         => 'Mo form them san pham',
        'POST /admin/product'                               => 'Tao san pham moi',
        'GET /admin/product/{id}/edit'                      => 'Mo form sua san pham #{id}',
        'PUT /admin/product/{id}'                           => 'Cap nhat san pham #{id}',
        'DELETE /admin/product/{id}'                        => 'Xoa san pham #{id}',
        'GET /admin/product/warehouse'                      => 'Xem kho va canh bao sale',
        'POST /admin/product/order-request'                 => 'Tao yeu cau nhap hang tu san pham',
        'GET /admin/orders'                                 => 'Xem danh sach don hang',
        'GET /admin/orders/{id}'                            => 'Xem chi tiet don hang #{id}',
        'POST /admin/orders/{id}/update-status'             => 'Cap nhat trang thai don hang #{id}',
        'GET /admin/orders/{id}/return'                     => 'Xem form hoan hang don #{id}',
        'POST /admin/orders/{id}/return'                    => 'Xu ly hoan hang don #{id}',
        'GET /admin/orders-damaged'                         => 'Xem danh sach hang hong',
        'GET /admin/warehouse/imports'                      => 'Xem danh sach nhap kho',
        'POST /admin/warehouse/imports/upload'              => 'Upload file nhap kho',
        'GET /admin/warehouse/imports/{id}'                 => 'Xem chi tiet nhap kho #{id}',
        'POST /admin/warehouse/imports/{id}/approve'        => 'Duyet nhap kho #{id}',
        'POST /admin/warehouse/imports/{id}/reject'         => 'Tu choi nhap kho #{id}',
        'GET /admin/supplier-offers'                        => 'Xem danh sach bao gia NSX',
        'GET /admin/supplier-offers/{id}'                   => 'Xem chi tiet bao gia #{id}',
        'POST /admin/supplier-offers/upload'                => 'Upload bao gia NSX',
        'POST /admin/supplier-offers/{id}/reject'           => 'Tu choi bao gia #{id}',
        'GET /admin/procurement'                            => 'Xem danh sach yeu cau nhap hang',
        'POST /admin/procurement'                           => 'Tao yeu cau nhap hang moi',
        'GET /admin/procurement/{id}'                       => 'Xem chi tiet yeu cau nhap hang #{id}',
        'POST /admin/procurement/{id}/close'                => 'Dong yeu cau nhap hang #{id}',
        'POST /admin/procurement/{id}/upload-offer'         => 'Upload bao gia cho yeu cau #{id}',
        'GET /admin/purchase-orders'                        => 'Xem danh sach don dat hang NSX',
        'POST /admin/purchase-orders'                       => 'Tao don dat hang NSX moi',
        'GET /admin/purchase-orders/{id}'                   => 'Xem chi tiet don dat hang #{id}',
        'POST /admin/purchase-orders/{id}/status'           => 'Cap nhat trang thai don dat hang #{id}',
        'POST /admin/purchase-orders/{id}/receive'          => 'Xac nhan nhan hang don #{id}',
        'GET /admin/category'                               => 'Xem danh sach danh muc',
        'POST /admin/category'                              => 'Tao danh muc moi',
        'PUT /admin/category/{id}'                          => 'Cap nhat danh muc #{id}',
        'DELETE /admin/category/{id}'                       => 'Xoa danh muc #{id}',
        'GET /admin/brand'                                  => 'Xem danh sach thuong hieu',
        'POST /admin/brand'                                 => 'Tao thuong hieu moi',
        'DELETE /admin/brand/{id}'                          => 'Xoa thuong hieu #{id}',
        'GET /admin/festival'                               => 'Xem danh sach festival',
        'POST /admin/festival'                              => 'Tao festival moi',
        'DELETE /admin/festival/{id}'                       => 'Xoa festival #{id}',
        'POST /admin/festival/{id}/products/update'         => 'Cap nhat san pham festival #{id}',
        'GET /admin/contacts'                               => 'Xem danh sach lien he',
        'DELETE /admin/contacts/{id}'                       => 'Xoa lien he #{id}',
        'GET /admin/footer'                                 => 'Xem danh sach footer',
        'POST /admin/footer'                                => 'Tao footer moi',
        'DELETE /admin/footer/{id}'                         => 'Xoa footer #{id}',
        'GET /admin/title'                                  => 'Xem danh sach title',
        'POST /admin/title'                                 => 'Tao title moi',
        'DELETE /admin/title/{id}'                          => 'Xoa title #{id}',
        'GET /admin/activity-log'                           => 'Xem lich su hoat dong root',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check() && Auth::user()->role === 'root') {
            $action = $this->resolveAction($request);
            if ($action) {
                try {
                    RootActivityLog::create([
                        'user_id'    => Auth::id(),
                        'user_name'  => Auth::user()->name,
                        'user_email' => Auth::user()->email,
                        'action'     => $action,
                        'created_at' => now(),
                    ]);
                } catch (\Throwable) {}
            }
        }

        return $response;
    }

    private function resolveAction(Request $request): ?string
    {
        $method = $request->method();
        $path = '/' . ltrim($request->path(), '/');

        foreach ($this->actionMap as $pattern => $description) {
            [$patternMethod, $patternPath] = explode(' ', $pattern, 2);
            if ($patternMethod !== $method) continue;

            $regex = '#^' . preg_replace('/\\\{id\\\}/', '(\d+)', preg_quote($patternPath, '#')) . '$#';
            if (preg_match($regex, $path, $matches)) {
                $action = $description;
                if (isset($matches[1])) {
                    $action = str_replace('{id}', $matches[1], $action);
                }
                return $action;
            }
        }

        if (str_starts_with($path, '/admin') && !in_array($method, ['GET', 'HEAD'], true)) {
            return "{$method} {$path}";
        }

        return null;
    }
}