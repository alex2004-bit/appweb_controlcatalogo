$user = \App\Models\User::with('currentTeam')->find(4);
if ($user) {
    echo "User: {$user->name} ({$user->email})\n";
    echo "current_team_id: " . ($user->current_team_id ?? 'null') . "\n";
    echo "currentTeam: " . ($user->currentTeam ? $user->currentTeam->name . ' (' . $user->currentTeam->slug . ')' : 'null') . "\n";
    echo "personalTeam: " . ($user->personalTeam() ? $user->personalTeam()->name . ' (' . $user->personalTeam()->slug . ')' : 'null') . "\n";
} else {
    echo "User not found\n";
}