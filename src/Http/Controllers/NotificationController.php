<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Listar notificações do usuário logado
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Pegar notificações com paginação
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Separar lidas e não lidas
        $unreadCount = $user->unreadNotifications()->count();
        
        return response()->json([
            'notifications' => $notifications->items(),
            'unread_count' => $unreadCount,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ]
        ]);
    }

    /**
     * Marcar notificação como lida
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return response()->json(['message' => 'Notificação não encontrada'], 404);
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'message' => 'Notificação marcada como lida',
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $user->unreadNotifications()->update(['read_at' => now()]);
        
        return response()->json([
            'message' => 'Todas as notificações foram marcadas como lidas',
            'unread_count' => 0
        ]);
    }

    /**
     * Deletar notificação
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return response()->json(['message' => 'Notificação não encontrada'], 404);
        }
        
        $notification->delete();
        
        return response()->json([
            'message' => 'Notificação deletada com sucesso',
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Deletar todas as notificações
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $user->notifications()->delete();
        
        return response()->json([
            'message' => 'Todas as notificações foram deletadas',
            'unread_count' => 0
        ]);
    }
}
