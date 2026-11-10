<p><strong>{{ $contact->name }}</strong> vous a écrit depuis le site.</p>
<p>Sujet : {{ $contact->subjectLabel() }}</p>
<p>E-mail : {{ $contact->email ?? '—' }}<br>Téléphone : {{ $contact->phone ?? '—' }}</p>
<hr>
<p style="white-space: pre-line">{{ $contact->message }}</p>