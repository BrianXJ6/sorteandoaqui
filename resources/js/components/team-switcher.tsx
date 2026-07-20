import { SidebarMenu, SidebarMenuItem } from "@/components/ui/sidebar"
import { GalleryVerticalEndIcon } from "lucide-react"

export function TeamSwitcher() {
    return (
        <SidebarMenu>
            <SidebarMenuItem className="flex items-center gap-3">
                <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                    <GalleryVerticalEndIcon />
                </div>
                <span className="truncate font-medium">{import.meta.env.VITE_APP_NAME}</span>
            </SidebarMenuItem>
        </SidebarMenu>
    )
}
