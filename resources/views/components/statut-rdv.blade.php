@props(['statut'])
<x-badge :tone="$statut->tone()">{{ $statut->label() }}</x-badge>
