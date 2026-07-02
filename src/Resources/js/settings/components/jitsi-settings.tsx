import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Video, Save } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { Switch } from '@/components/ui/switch';

interface JitsiMeetingSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

export default function JitsiMeetingSettings({ userSettings = {}, auth }: JitsiMeetingSettingsProps) {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const canEdit = auth?.user?.permissions?.includes('edit-jitsi-settings');

  const [settings, setSettings] = useState({
    jitsi_enabled: userSettings?.jitsi_enabled === 'on',
    jitsi_domain: userSettings?.jitsi_domain || '',
    jitsi_app_id: userSettings?.jitsi_app_id || '',
    jitsi_jwt_secret: userSettings?.jitsi_jwt_secret || ''
  });

  useEffect(() => {
    setSettings({
      jitsi_enabled: userSettings?.jitsi_enabled === 'on',
      jitsi_domain: userSettings?.jitsi_domain || '',
      jitsi_app_id: userSettings?.jitsi_app_id || '',
      jitsi_jwt_secret: userSettings?.jitsi_jwt_secret || ''
    });
  }, [userSettings]);

  const handleSettingsChange = (field: string, value: string | boolean) => {
    setSettings(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const saveSettings = () => {
    setIsLoading(true);

    router.post(route('jitsi.settings.update'), {
      settings: {
        ...settings,
        jitsi_enabled: settings.jitsi_enabled ? 'on' : 'off'
      }
    }, {
      preserveScroll: true,
      onSuccess: () => {
        setIsLoading(false);
      },
      onError: () => {
        setIsLoading(false);
      }
    });
  };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <div className="order-1 rtl:order-2">
          <CardTitle className="flex items-center gap-2 text-lg">
            <Video className="h-5 w-5" />
            {t('Jitsi Meet Settings')}
          </CardTitle>
          <p className="text-sm text-muted-foreground mt-1">
            {t('Configure Jitsi Meet integration settings')}
          </p>
        </div>
        {canEdit && (
          <Button className="order-2 rtl:order-1" onClick={saveSettings} disabled={isLoading} size="sm">
            <Save className="h-4 w-4 mr-2" />
            {isLoading ? t('Saving...') : t('Save Changes')}
          </Button>
        )}
      </CardHeader>
      <CardContent>
        <div className="space-y-6">
          {/* Enable/Disable Jitsi */}
          <div className="flex items-center justify-between p-4 border rounded-lg">
            <div>
              <Label htmlFor="jitsi_enabled" className="text-base font-medium">
                {t('Enable Jitsi Meet Integration')}
              </Label>
              <p className="text-sm text-muted-foreground mt-1">
                {t('Allow meetings to be created via Jitsi Meet — no account or API key required by default')}
              </p>
            </div>
            <Switch
              id="jitsi_enabled"
              checked={settings.jitsi_enabled}
              onCheckedChange={(checked) => handleSettingsChange('jitsi_enabled', checked)}
              disabled={!canEdit}
            />
          </div>

          {settings.jitsi_enabled && (
            <>
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div className="space-y-6">
                  <div className="space-y-3">
                    <Label htmlFor="jitsi_domain">{t('Self-Hosted Domain (optional)')}</Label>
                    <Input
                      id="jitsi_domain"
                      value={settings.jitsi_domain}
                      onChange={(e) => handleSettingsChange('jitsi_domain', e.target.value)}
                      placeholder={t('Leave blank to use meet.jit.si')}
                      disabled={!canEdit}
                    />
                  </div>

                  <div className="space-y-3">
                    <Label htmlFor="jitsi_app_id">{t('JaaS App ID (optional)')}</Label>
                    <Input
                      id="jitsi_app_id"
                      value={settings.jitsi_app_id}
                      onChange={(e) => handleSettingsChange('jitsi_app_id', e.target.value)}
                      placeholder={t('Only needed for JaaS / authenticated rooms')}
                      disabled={!canEdit}
                    />
                  </div>

                  <div className="space-y-3">
                    <Label htmlFor="jitsi_jwt_secret">{t('JWT Secret (optional)')}</Label>
                    <Input
                      id="jitsi_jwt_secret"
                      value={settings.jitsi_jwt_secret}
                      onChange={(e) => handleSettingsChange('jitsi_jwt_secret', e.target.value)}
                      placeholder={t('Only needed for JaaS / authenticated rooms')}
                      disabled={!canEdit}
                      type="password"
                    />
                  </div>
                </div>

                <div className="border rounded-lg p-4 bg-blue-50/50 border-blue-200">
                  <h4 className="font-medium mb-2 text-blue-900">{t('Setup Instructions')}</h4>
                  <div className="space-y-2 text-sm text-blue-800">
                    <p>{t('Jitsi Meet works out of the box on the free, public')} <a href="https://meet.jit.si" target="_blank" rel="noopener noreferrer" className="text-blue-600 underline hover:text-blue-800">meet.jit.si</a> {t('instance — no setup required.')}</p>
                    <p>{t('To use your own self-hosted Jitsi server, enter its domain above.')}</p>
                    <p>{t('To use Jitsi as a Service (JaaS) with authenticated rooms, add your App ID and JWT secret from')} <a href="https://jaas.8x8.vc" target="_blank" rel="noopener noreferrer" className="text-blue-600 underline hover:text-blue-800">jaas.8x8.vc</a>.</p>
                  </div>
                </div>
              </div>
            </>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
