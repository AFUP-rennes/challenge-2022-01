<?php
require_once __DIR__ . '/vendor/autoload.php';

$board = new OpponentBoard;
$fleet = new PersonalBoard;
while (true) {
    $command = fgets(STDIN);
    if ($command === false) {
        Response::fromError("could not read STDIN")->send();
        die();
    }
    $request = Request::from(trim($command));
    if (Request::COORDINATES()->getKey() === $request->getKey()) {
        $fleet->hitCell($request->getValue())
            ->send();
    } elseif ($request->equals(Request::YOUR_TURN())) {
        Response::fromCoordinates(
            $board->takeDecision()
        )->send();
    } elseif ($request->equals(Request::HIT()) || $request->equals(Request::MISS()) || $request->equals(Request::SUNK())) {
        $board->saveResult($request);
        Response::OK()->send();
    } elseif ($request->equals(Request::WON())) {
        Response::OK()->send();
        break;
    } else {
        Response::fromError("Can't understand '$command'")->send();
        die();
    }
}