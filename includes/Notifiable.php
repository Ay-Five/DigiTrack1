<?php
/**
 * Notifiable.php
 *
 * OOP PILLAR: POLYMORPHISM (via Interface)
 * -----------------------------------------
 * An interface defines a contract: any class that implements Notifiable
 * MUST provide these three methods.  The caller doesn't care whether it's
 * talking to an AdminDashboard, InstructorDashboard, or StudentDashboard —
 * it just calls the same methods on whichever object it has.
 *
 * Example of polymorphism in action:
 *
 *   function markAllRead(Notifiable $dashboard): void {
 *       $dashboard->markNotificationsRead();   // works for ALL three roles
 *   }
 *
 * The correct role-specific SQL runs automatically depending on which
 * concrete class is behind the $dashboard variable.  That's polymorphism.
 */
interface Notifiable
{
    /**
     * Return the count of unread notifications for this user.
     */
    public function getUnreadCount(): int;

    /**
     * Mark all of this user's notifications as read.
     */
    public function markNotificationsRead(): void;

    /**
     * Return the latest notifications for display in the sidebar / bell icon.
     *
     * @param  int    $limit  Max rows to return
     * @return array          Array of notification rows
     */
    public function getNotifications(int $limit = 30): array;
}
