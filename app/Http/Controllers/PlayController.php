public function show($contestId, $participantId)
{
    // Vérifier si le cookie anti-triche existe
    if (Cookie::has('supprimer_ce_cookie_revient_a_tricher')) {
        return redirect()->route('home')->with('error', 'Vous avez déjà participé. Une seule participation par appareil est autorisée.');
    }

    // Si pas de cookie, afficher la page de jeu
    return view('play', [
        'contestId' => $contestId,
        'participantId' => $participantId
    ]);
}
