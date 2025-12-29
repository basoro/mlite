import React from 'react';
import { Navigate } from 'react-router-dom';
import Sidebar from './Sidebar';
import Header from './Header';
import { useAuth } from '@/contexts/AuthContext';
import { MessageCircle, X, Send, Search, QrCode } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { getContacts, getMessages, sendMessage, getWAStatus } from '@/lib/api';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { format } from 'date-fns';
import { QRCodeSVG } from 'qrcode.react';

interface MainLayoutProps {
  children: React.ReactNode;
}

const MainLayout: React.FC<MainLayoutProps> = ({ children }) => {
  const { isAuthenticated, isLoading } = useAuth();
  const [isSidebarOpen, setIsSidebarOpen] = React.useState(false);
  const [isSidebarCollapsed, setIsSidebarCollapsed] = React.useState(false);
  const [isChatOpen, setIsChatOpen] = React.useState(false);
  const [selectedContact, setSelectedContact] = React.useState<any>(null);
  const [messageText, setMessageText] = React.useState('');
  const messagesEndRef = React.useRef<HTMLDivElement>(null);
  const queryClient = useQueryClient();

  const { data: waStatus } = useQuery({
    queryKey: ['wa-status'],
    queryFn: getWAStatus,
    enabled: isChatOpen,
    refetchInterval: 3000,
  });

  const { data: contactsData } = useQuery({
    queryKey: ['wa-contacts'],
    queryFn: getContacts,
    enabled: isChatOpen && waStatus?.connected,
    refetchInterval: 5000,
  });

  const { data: messagesData } = useQuery({
    queryKey: ['wa-messages', selectedContact?.id],
    queryFn: () => getMessages(selectedContact.id),
    enabled: !!selectedContact && waStatus?.connected,
    refetchInterval: 2000,
  });

  const sendMessageMutation = useMutation({
    mutationFn: (data: { to: string; message: string }) => sendMessage(data.to, data.message),
    onSuccess: () => {
      setMessageText('');
      queryClient.invalidateQueries({ queryKey: ['wa-messages', selectedContact?.id] });
    },
  });

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  React.useEffect(() => {
    scrollToBottom();
  }, [messagesData]);

  const handleSendMessage = (e: React.FormEvent) => {
    e.preventDefault();
    if (!messageText.trim() || !selectedContact) return;
    sendMessageMutation.mutate({ to: selectedContact.phone, message: messageText });
  };

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <div className="animate-pulse-soft">
          <div className="w-12 h-12 rounded-xl bg-primary flex items-center justify-center">
            <span className="text-primary-foreground font-bold text-xl">m</span>
          </div>
        </div>
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return (
    <div className="flex min-h-screen bg-background relative">
      {/* Overlay for mobile sidebar */}
      {isSidebarOpen && (
        <div 
          className="fixed inset-0 bg-black/50 z-40 lg:hidden"
          onClick={() => setIsSidebarOpen(false)}
        />
      )}

      {/* Sidebar - Hidden on mobile unless toggled */}
      <div className={`fixed lg:sticky lg:top-0 lg:h-screen lg:max-h-screen inset-y-0 left-0 z-50 transform ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0 transition-transform duration-200 ease-in-out`}>
        <Sidebar onClose={() => setIsSidebarOpen(false)} isCollapsed={isSidebarCollapsed} />
      </div>

      <div className="flex-1 flex flex-col min-w-0">
        <Header 
          onMenuClick={() => setIsSidebarOpen(!isSidebarOpen)} 
          onToggleCollapse={() => setIsSidebarCollapsed(!isSidebarCollapsed)}
        />
        <main className="flex-1 p-4 lg:p-6 overflow-auto">
          {children}
        </main>
      </div>

      {/* Floating Action Button for Chat */}
      <Button
        className="fixed bottom-6 right-6 h-14 w-14 rounded-full shadow-lg bg-emerald-600 hover:bg-emerald-700 z-50"
        onClick={() => setIsChatOpen(!isChatOpen)}
      >
        {isChatOpen ? <X className="h-6 w-6" /> : <MessageCircle className="h-6 w-6" />}
      </Button>

      {/* Chat Window */}
      {isChatOpen && (
        <div className="fixed bottom-24 right-6 w-[800px] h-[600px] bg-white rounded-lg shadow-2xl border border-gray-200 z-50 flex overflow-hidden animate-in slide-in-from-bottom-10 fade-in">
          {/* Check connection status */}
          {!waStatus?.connected ? (
            <div className="w-full h-full flex flex-col items-center justify-center bg-white p-8">
              <h2 className="text-2xl font-semibold text-gray-800 mb-2">WhatsApp Disconnected</h2>
              <p className="text-gray-500 mb-8 text-center max-w-md">
                Scan QR Code berikut menggunakan WhatsApp di HP Anda untuk menghubungkan layanan.
              </p>
              
              {waStatus?.qr ? (
                <div className="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                  <QRCodeSVG value={waStatus.qr} size={256} level="H" />
                </div>
              ) : (
                <div className="w-64 h-64 bg-gray-100 rounded-xl flex items-center justify-center animate-pulse">
                  <QrCode className="w-16 h-16 text-gray-300" />
                </div>
              )}
              
              <div className="mt-8 text-sm text-gray-400">
                {waStatus?.connecting ? 'Menghubungkan...' : 'Menunggu QR Code...'}
              </div>
            </div>
          ) : (
            <>
              {/* Left Sidebar - Contacts */}
              <div className="w-1/3 border-r border-gray-200 bg-gray-50 flex flex-col">
                <div className="p-4 border-b border-gray-200 bg-white">
                  <div className="flex justify-between items-center mb-2">
                    <h3 className="font-semibold text-gray-800">WhatsApp</h3>
                    <div className="flex items-center gap-1">
                      <div className="w-2 h-2 rounded-full bg-emerald-500"></div>
                      <span className="text-[10px] text-emerald-600 font-medium">Connected</span>
                    </div>
                  </div>
                  <div className="relative">
                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-gray-400" />
                    <Input placeholder="Cari kontak..." className="pl-8 h-9 text-sm bg-gray-100 border-none" />
                  </div>
                </div>
                <div className="flex-1 overflow-y-auto">
                  {contactsData?.data?.map((contact: any) => (
                    <div
                      key={contact.id}
                      className={`p-3 cursor-pointer hover:bg-white transition-colors border-b border-gray-100 ${
                        selectedContact?.id === contact.id ? 'bg-white border-l-4 border-l-emerald-500' : ''
                      }`}
                      onClick={() => setSelectedContact(contact)}
                    >
                      <div className="flex justify-between items-start mb-1">
                        <span className="font-medium text-gray-900 truncate max-w-[120px]">{contact.name || contact.phone}</span>
                        <span className="text-[10px] text-gray-500">
                          {contact.last_message_at ? format(new Date(contact.last_message_at), 'HH:mm') : ''}
                        </span>
                      </div>
                      <div className="flex justify-between items-center">
                        <p className="text-xs text-gray-500 truncate max-w-[150px]">
                          {contact.phone}
                        </p>
                        {contact.unread_count > 0 && (
                          <span className="bg-emerald-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">
                            {contact.unread_count}
                          </span>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Right Content - Chat Messages */}
              <div className="w-2/3 flex flex-col bg-[#e5ddd5]">
                {selectedContact ? (
                  <>
                    {/* Chat Header */}
                    <div className="p-3 bg-white border-b border-gray-200 flex items-center justify-between">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                          <UserIcon name={selectedContact.name} />
                        </div>
                        <div>
                          <h4 className="font-medium text-gray-900 text-sm">{selectedContact.name || selectedContact.phone}</h4>
                          <p className="text-xs text-gray-500">{selectedContact.phone}</p>
                        </div>
                      </div>
                    </div>

                    {/* Messages Area */}
                    <div className="flex-1 overflow-y-auto p-4 space-y-3 bg-[url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png')]">
                      {messagesData?.data?.map((msg: any) => (
                        <div
                          key={msg.id}
                          className={`flex ${msg.direction === 'out' ? 'justify-end' : 'justify-start'}`}
                        >
                          <div
                            className={`max-w-[70%] rounded-lg p-2 px-3 text-sm shadow-sm relative ${
                              msg.direction === 'out' 
                                ? 'bg-[#d9fdd3] rounded-tr-none' 
                                : 'bg-white rounded-tl-none'
                            }`}
                          >
                            <p className="text-gray-800 leading-relaxed whitespace-pre-wrap">{msg.text}</p>
                            <div className="text-[10px] text-gray-500 text-right mt-1 flex items-center justify-end gap-1">
                              {format(new Date(msg.created_at), 'HH:mm')}
                              {msg.direction === 'out' && (
                                <span className={msg.status === 'read' ? 'text-blue-500' : 'text-gray-400'}>
                                  ✓✓
                                </span>
                              )}
                            </div>
                          </div>
                        </div>
                      ))}
                      <div ref={messagesEndRef} />
                    </div>

                    {/* Input Area */}
                    <form onSubmit={handleSendMessage} className="p-3 bg-white flex gap-2 items-center">
                      <Input
                        value={messageText}
                        onChange={(e) => setMessageText(e.target.value)}
                        placeholder="Ketik pesan..."
                        className="flex-1 bg-gray-100 border-none focus-visible:ring-0"
                      />
                      <Button 
                        type="submit" 
                        size="icon" 
                        className="bg-emerald-600 hover:bg-emerald-700 h-10 w-10 shrink-0"
                        disabled={sendMessageMutation.isPending || !messageText.trim()}
                      >
                        <Send className="h-5 w-5" />
                      </Button>
                    </form>
                  </>
                ) : (
                  <div className="flex-1 flex flex-col items-center justify-center text-gray-500 bg-[#f0f2f5]">
                    <div className="w-64 h-64 bg-gray-200 rounded-full flex items-center justify-center mb-4 opacity-50">
                      <MessageCircle className="w-32 h-32 text-gray-400" />
                    </div>
                    <p className="text-lg font-medium">WhatsApp Web Gateway</p>
                    <p className="text-sm mt-2">Pilih kontak untuk mulai chatting</p>
                  </div>
                )}
              </div>
            </>
          )}
        </div>
      )}
    </div>
  );
};

const UserIcon = ({ name }: { name: string }) => {
  const initial = name ? name.charAt(0).toUpperCase() : '?';
  return <span className="font-bold text-sm">{initial}</span>;
};

export default MainLayout;
