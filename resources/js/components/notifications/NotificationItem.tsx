import * as React from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { formatDistanceToNow } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { Notification } from '@raptor/hooks/useNotifications';

// 🔔 Importar ícones necessários
import {
  CheckCircle, AlertTriangle, Info, AlertCircle, Star,
  Mail, Settings, Download, FileText, Users, 
  Calendar, Bell, Shield, Gift, X, Eye, Trash2
} from 'lucide-react';

// 🔔 Mapeamento de ícones
const notificationIconMap = {
  'CheckCircle': CheckCircle,
  'AlertTriangle': AlertTriangle, 
  'Info': Info,
  'AlertCircle': AlertCircle,
  'Star': Star,
  'Mail': Mail,
  'Settings': Settings,
  'Download': Download,
  'FileText': FileText,
  'Users': Users,
  'Calendar': Calendar,
  'Bell': Bell,
  'Shield': Shield,
  'Gift': Gift,
} as const;

// 🔔 Mapeamento de cores
const notificationColorMap = {
  'success': 'text-green-600 bg-green-50 border-green-200',
  'error': 'text-red-600 bg-red-50 border-red-200',
  'warning': 'text-yellow-600 bg-yellow-50 border-yellow-200',
  'info': 'text-blue-600 bg-blue-50 border-blue-200',
  'primary': 'text-purple-600 bg-purple-50 border-purple-200',
} as const;

interface NotificationItemProps {
  notification: Notification;
  onMarkAsRead: (id: string) => void;
  onDelete: (id: string) => void;
}

export function NotificationItem({ 
  notification, 
  onMarkAsRead, 
  onDelete 
}: NotificationItemProps) {
  const isUnread = !notification.read_at;
  const IconComponent = notification.data.icon 
    ? notificationIconMap[notification.data.icon as keyof typeof notificationIconMap] 
    : Bell;
  
  const colorClass = notification.data.color 
    ? notificationColorMap[notification.data.color] 
    : notificationColorMap.info;

  // Formatear data relativa
  const timeAgo = formatDistanceToNow(new Date(notification.created_at), {
    addSuffix: true,
    locale: ptBR,
  });

  return (
    <div 
      className={cn(
        "flex items-start gap-3 p-3 hover:bg-muted/50 transition-colors",
        isUnread && "bg-blue-50/50 border-l-4 border-l-blue-500"
      )}
    >
      {/* Ícone da notificação */}
      <div className={cn(
        "flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center border",
        colorClass
      )}>
        <IconComponent className="w-4 h-4" />
      </div>

      {/* Conteúdo da notificação */}
      <div className="flex-1 min-w-0">
        <div className="flex items-start justify-between gap-2">
          <div className="flex-1">
            {/* Título */}
            <h4 className={cn(
              "text-sm font-medium text-foreground",
              isUnread && "font-semibold"
            )}>
              {notification.data.title}
              {isUnread && (
                <Badge variant="secondary" className="ml-2 text-xs">
                  Nova
                </Badge>
              )}
            </h4>

            {/* Mensagem */}
            <p className="text-sm text-muted-foreground mt-1 line-clamp-2">
              {notification.data.message}
            </p>

            {/* Data */}
            <span className="text-xs text-muted-foreground mt-2 block">
              {timeAgo}
            </span>
          </div>

          {/* Botões de ação */}
          <div className="flex items-center gap-1 flex-shrink-0">
            {isUnread && (
              <Button
                variant="ghost"
                size="sm"
                onClick={() => onMarkAsRead(notification.id)}
                className="h-8 px-2 text-xs"
                title="Marcar como lida"
              >
                <Eye className="w-4 h-4" />
              </Button>
            )}
            
            <Button
              variant="ghost"
              size="sm"
              onClick={() => onDelete(notification.id)}
              className="h-8 px-2 text-xs text-red-600 hover:text-red-700 hover:bg-red-50"
              title="Deletar notificação"
            >
              <Trash2 className="w-4 h-4" />
            </Button>
          </div>
        </div>

        {/* Botão de ação da notificação */}
        {notification.data.action_url && notification.data.action_text && (
          <div className="mt-3">
            <Link
              href={notification.data.action_url}
              className="inline-flex items-center text-sm text-primary hover:text-primary/80 font-medium"
            >
              {notification.data.action_text}
            </Link>
          </div>
        )}
      </div>
    </div>
  );
} 