import React, { useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Clock, Calendar, ListOrdered, Loader2 } from 'lucide-react';

interface UpdateConfirmationDialogProps {
    open: boolean;
    onClose: () => void;
    onConfirm: (type: 'all' | 'partial' | 'none', selected?: string[]) => void;
    updates: {
        schedule_updates: Array<{ task_id: string; task_name: string; time: string }>;
        duration_updates: Array<{ task_id: string; task_name: string; duration: number }>;
        order_updates: Array<{ task_id: string; task_name: string; order: number }>;
    };
    loading: boolean;
}

export function UpdateConfirmationDialog({
    open,
    onClose,
    onConfirm,
    updates,
    loading
}: UpdateConfirmationDialogProps) {
    const [updateType, setUpdateType] = useState<'all' | 'partial' | 'none'>('all');
    const [selectedUpdates, setSelectedUpdates] = useState<string[]>(['schedule', 'duration', 'order']);

    const handleConfirm = () => {
        if (updateType === 'partial') {
            onConfirm(updateType, selectedUpdates);
        } else {
            onConfirm(updateType);
        }
    };

    const toggleUpdate = (type: string) => {
        setSelectedUpdates(prev =>
            prev.includes(type)
                ? prev.filter(t => t !== type)
                : [...prev, type]
        );
    };

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="max-w-2xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>✅ Confirmation de mise à jour</DialogTitle>
                    <DialogDescription>
                        Souhaitez-vous que je mette à jour automatiquement vos tâches dans Todoist selon cette planification ?
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-6 py-4">
                    {/* Update Summary */}
                    <div className="space-y-4">
                        <h4 className="font-semibold">🔄 Modifications à apporter dans Todoist</h4>
                        
                        {/* Schedule Updates */}
                        {updates.schedule_updates.length > 0 && (
                            <div className="space-y-2">
                                <div className="flex items-center gap-2">
                                    <Clock className="h-4 w-4" />
                                    <span className="font-medium">Heures à assigner :</span>
                                </div>
                                <ul className="ml-6 space-y-1 text-sm text-muted-foreground">
                                    {updates.schedule_updates.slice(0, 3).map((update, index) => (
                                        <li key={index}>
                                            • {update.task_name} : Programmer à {update.time}
                                        </li>
                                    ))}
                                    {updates.schedule_updates.length > 3 && (
                                        <li>• ... et {updates.schedule_updates.length - 3} autres tâches</li>
                                    )}
                                </ul>
                            </div>
                        )}

                        {/* Duration Updates */}
                        {updates.duration_updates.length > 0 && (
                            <div className="space-y-2">
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4" />
                                    <span className="font-medium">Durées à définir :</span>
                                </div>
                                <ul className="ml-6 space-y-1 text-sm text-muted-foreground">
                                    {updates.duration_updates.slice(0, 3).map((update, index) => (
                                        <li key={index}>
                                            • {update.task_name} : Définir durée {update.duration} min
                                        </li>
                                    ))}
                                    {updates.duration_updates.length > 3 && (
                                        <li>• ... et {updates.duration_updates.length - 3} autres tâches</li>
                                    )}
                                </ul>
                            </div>
                        )}

                        {/* Order Updates */}
                        {updates.order_updates.length > 0 && (
                            <div className="space-y-2">
                                <div className="flex items-center gap-2">
                                    <ListOrdered className="h-4 w-4" />
                                    <span className="font-medium">Ordre dans la vue "Aujourd'hui" :</span>
                                </div>
                                <p className="ml-6 text-sm text-muted-foreground">
                                    {updates.order_updates.length} tâches seront réordonnées selon le planning
                                </p>
                            </div>
                        )}
                    </div>

                    {/* Update Options */}
                    <div className="space-y-4">
                        <h4 className="font-semibold">Options de mise à jour</h4>
                        
                        <div className="space-y-3">
                            <div className="flex items-center space-x-2">
                                <input
                                    type="radio"
                                    id="update-all"
                                    name="update-type"
                                    value="all"
                                    checked={updateType === 'all'}
                                    onChange={() => setUpdateType('all')}
                                    className="h-4 w-4"
                                />
                                <Label htmlFor="update-all" className="cursor-pointer">
                                    <strong>OUI</strong> - Appliquer toutes les modifications
                                </Label>
                            </div>

                            <div className="flex items-center space-x-2">
                                <input
                                    type="radio"
                                    id="update-none"
                                    name="update-type"
                                    value="none"
                                    checked={updateType === 'none'}
                                    onChange={() => setUpdateType('none')}
                                    className="h-4 w-4"
                                />
                                <Label htmlFor="update-none" className="cursor-pointer">
                                    <strong>NON</strong> - Conserver le planning sans modifier Todoist
                                </Label>
                            </div>

                            <div className="flex items-center space-x-2">
                                <input
                                    type="radio"
                                    id="update-partial"
                                    name="update-type"
                                    value="partial"
                                    checked={updateType === 'partial'}
                                    onChange={() => setUpdateType('partial')}
                                    className="h-4 w-4"
                                />
                                <Label htmlFor="update-partial" className="cursor-pointer">
                                    <strong>PARTIEL</strong> - Choisir les modifications à appliquer
                                </Label>
                            </div>
                        </div>

                        {updateType === 'partial' && (
                            <div className="ml-6 space-y-2 p-4 bg-muted rounded-lg">
                                <p className="text-sm font-medium mb-2">
                                    Sélectionnez les modifications à appliquer :
                                </p>
                                <div className="space-y-2">
                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="update-schedule"
                                            checked={selectedUpdates.includes('schedule')}
                                            onCheckedChange={() => toggleUpdate('schedule')}
                                        />
                                        <Label htmlFor="update-schedule" className="cursor-pointer">
                                            Heures de planification
                                        </Label>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="update-duration"
                                            checked={selectedUpdates.includes('duration')}
                                            onCheckedChange={() => toggleUpdate('duration')}
                                        />
                                        <Label htmlFor="update-duration" className="cursor-pointer">
                                            Durées estimées
                                        </Label>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="update-order"
                                            checked={selectedUpdates.includes('order')}
                                            onCheckedChange={() => toggleUpdate('order')}
                                        />
                                        <Label htmlFor="update-order" className="cursor-pointer">
                                            Ordre des tâches
                                        </Label>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>

                    <Alert>
                        <AlertDescription>
                            💡 <strong>Conseil :</strong> Activez les notifications Todoist pour respecter votre planning !
                        </AlertDescription>
                    </Alert>
                </div>

                <DialogFooter>
                    <Button variant="outline" onClick={onClose} disabled={loading}>
                        Annuler
                    </Button>
                    <Button onClick={handleConfirm} disabled={loading}>
                        {loading ? (
                            <>
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                Mise à jour...
                            </>
                        ) : (
                            'Confirmer'
                        )}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}