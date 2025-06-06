import * as React from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Bell, CheckCheck, Trash2, Loader2 } from 'lucide-react';
import { useNotifications } from '@raptor/hooks/useNotifications';
import { NotificationItem } from './NotificationItem';
import { cn } from '@/lib/utils';

interface NotificationDropdownProps {
  className?: string;
}

export function NotificationDropdown({ className }: NotificationDropdownProps) {
  const {
    notifications,
    unreadCount,
    loading,
    error,
    hasUnread,
    isEmpty,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    deleteAllNotifications,
    fetchNotifications,
  } = useNotifications();

  const [isOpen, setIsOpen] = React.useState(false);

  // Atualizar notificações quando o dropdown abrir
  React.useEffect(() => {
    if (isOpen) {
      fetchNotifications();
    }
  }, [isOpen, fetchNotifications]);

  return (
    <DropdownMenu open={isOpen} onOpenChange={setIsOpen}>
      <DropdownMenuTrigger asChild>
        <Button
          variant="ghost"
          size="sm"
          className={cn(
            "relative h-9 w-9 rounded-full",
            className
          )}
          title="Notificações"
        >
          <Bell className="h-4 w-4" />
          {hasUnread && (
            <Badge 
              variant="destructive" 
              className="absolute -top-1 -right-1 h-5 w-5 p-0 flex items-center justify-center text-xs"
            >
              {unreadCount > 99 ? '99+' : unreadCount}
            </Badge>
          )}
        </Button>
      </DropdownMenuTrigger>
      
      <DropdownMenuContent 
        align="end" 
        className="w-80 p-0 max-h-[70vh]"
        side="bottom"
        sideOffset={8}
      >
        {/* Header do dropdown */}
        <div className="flex items-center justify-between p-4 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
          <div className="flex items-center gap-2">
            <h3 className="font-semibold text-sm">Notificações</h3>
            {hasUnread && (
              <Badge variant="secondary" className="text-xs">
                {unreadCount} nova{unreadCount !== 1 ? 's' : ''}
              </Badge>
            )}
          </div>
          
          <div className="flex items-center gap-1">
            {hasUnread && (
              <Button
                variant="ghost"
                size="sm"
                onClick={markAllAsRead}
                className="h-8 px-2 text-xs"
                title="Marcar todas como lidas"
                disabled={loading}
              >
                <CheckCheck className="w-4 h-4" />
              </Button>
            )}
            
            {!isEmpty && (
              <Button
                variant="ghost"
                size="sm"
                onClick={deleteAllNotifications}
                className="h-8 px-2 text-xs text-red-600 hover:text-red-700 hover:bg-red-50"
                title="Deletar todas"
                disabled={loading}
              >
                <Trash2 className="w-4 h-4" />
              </Button>
            )}
          </div>
        </div>

        {/* Conteúdo do dropdown */}
        {loading && (
          <div className="flex items-center justify-center p-8">
            <Loader2 className="w-6 h-6 animate-spin text-muted-foreground" />
            <span className="ml-2 text-sm text-muted-foreground">
              Carregando notificações...
            </span>
          </div>
        )}

        {error && (
          <div className="p-4 text-center">
            <p className="text-sm text-red-600 mb-2">
              Erro ao carregar notificações
            </p>
            <Button
              variant="outline"
              size="sm"
              onClick={() => fetchNotifications()}
            >
              Tentar novamente
            </Button>
          </div>
        )}

        {!loading && !error && isEmpty && (
          <div className="p-8 text-center">
            <Bell className="w-12 h-12 text-muted-foreground mx-auto mb-3" />
            <p className="text-sm text-muted-foreground mb-1">
              Nenhuma notificação
            </p>
            <p className="text-xs text-muted-foreground">
              Você está em dia com suas notificações!
            </p>
          </div>
        )}

        {!loading && !error && !isEmpty && (
          <ScrollArea className="h-96 max-h-[50vh]">
            <div className="divide-y divide-border">
              {notifications.map((notification) => (
                <NotificationItem
                  key={notification.id}
                  notification={notification}
                  onMarkAsRead={markAsRead}
                  onDelete={deleteNotification}
                />
              ))}
            </div>
          </ScrollArea>
        )}

        {/* Footer com link para ver todas (se necessário) */}
        {!isEmpty && !loading && !error && (
          <>
            <Separator className="bg-border/50" />
            <div className="p-3 bg-muted/30">
              <Button
                variant="ghost"
                size="sm"
                className="w-full text-xs hover:bg-background/80"
                onClick={() => {
                  setIsOpen(false);
                  // Aqui poderia navegar para página de notificações
                  // router.visit('/notifications');
                }}
              >
                Ver todas as notificações
              </Button>
            </div>
          </>
        )}
      </DropdownMenuContent>
    </DropdownMenu>
  );
} 