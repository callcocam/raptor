import { useState, useEffect, useCallback } from 'react';
import { router } from '@inertiajs/react';
import { useToast } from './useToast';

// 🔔 Tipos para notificações
export interface Notification {
  id: string;
  type: string;
  notifiable_type: string;
  notifiable_id: string;
  data: {
    title: string;
    message: string;
    action_url?: string;
    action_text?: string;
    type: string;
    icon?: string;
    color?: 'success' | 'error' | 'warning' | 'info' | 'primary';
  };
  read_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface NotificationPagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface NotificationsResponse {
  notifications: Notification[];
  unread_count: number;
  pagination: NotificationPagination;
}

// 🔔 Hook personalizado para notificações
export function useNotifications() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [pagination, setPagination] = useState<NotificationPagination>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const { toast } = useToast();

  // 🔥 Função auxiliar para obter CSRF token
  const getCsrfToken = useCallback(() => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }, []);

  // 🔥 Buscar notificações
  const fetchNotifications = useCallback(async (page: number = 1) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await fetch(`/admin/notifications?page=${page}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data: NotificationsResponse = await response.json();
      
      setNotifications(data.notifications);
      setUnreadCount(data.unread_count);
      setPagination(data.pagination);
      
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao carregar notificações');
      console.error('Erro ao buscar notificações:', err);
    } finally {
      setLoading(false);
    }
  }, []);

  // 🔥 Marcar notificação como lida
  const markAsRead = useCallback(async (notificationId: string) => {
    try {
      const response = await fetch(`/admin/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      
      // Atualizar estado local
      setNotifications(prev => 
        prev.map(notification => 
          notification.id === notificationId 
            ? { ...notification, read_at: new Date().toISOString() }
            : notification
        )
      );
      setUnreadCount(data.unread_count);
      
    } catch (err) {
      console.error('Erro ao marcar notificação como lida:', err);
      toast.error('Erro ao marcar notificação como lida');
    }
  }, [getCsrfToken, toast]);

  // 🔥 Marcar todas como lidas
  const markAllAsRead = useCallback(async () => {
    try {
      const response = await fetch('/admin/notifications/read-all', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      
      // Atualizar estado local
      setNotifications(prev => 
        prev.map(notification => ({ 
          ...notification, 
          read_at: new Date().toISOString() 
        }))
      );
      setUnreadCount(0);
      
      toast.success('Todas as notificações foram marcadas como lidas');
      
    } catch (err) {
      console.error('Erro ao marcar todas como lidas:', err);
      toast.error('Erro ao marcar todas as notificações como lidas');
    }
  }, [getCsrfToken, toast]);

  // 🔥 Deletar notificação
  const deleteNotification = useCallback(async (notificationId: string) => {
    try {
      const response = await fetch(`/admin/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      
      // Remover do estado local
      setNotifications(prev => prev.filter(notification => notification.id !== notificationId));
      setUnreadCount(data.unread_count);
      
      toast.success('Notificação deletada com sucesso');
      
    } catch (err) {
      console.error('Erro ao deletar notificação:', err);
      toast.error('Erro ao deletar notificação');
    }
  }, [getCsrfToken, toast]);

  // 🔥 Deletar todas as notificações
  const deleteAllNotifications = useCallback(async () => {
    try {
      const response = await fetch('/admin/notifications', {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      // Limpar estado local
      setNotifications([]);
      setUnreadCount(0);
      
      toast.success('Todas as notificações foram deletadas');
      
    } catch (err) {
      console.error('Erro ao deletar todas as notificações:', err);
      toast.error('Erro ao deletar todas as notificações');
    }
  }, [getCsrfToken, toast]);

  // 🔥 Carregar notificações ao montar o hook
  useEffect(() => {
    fetchNotifications();
  }, [fetchNotifications]);

  // 🔥 Retornar estado e funções
  return {
    notifications,
    unreadCount,
    pagination,
    loading,
    error,
    // Funções
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    deleteAllNotifications,
    // Utilities
    hasUnread: unreadCount > 0,
    isEmpty: notifications.length === 0,
  };
} 