<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica - {{ $patient->nombre }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { width: 100%; border-bottom: 2px solid #1e40af; padding-bottom: 15px; margin-bottom: 20px; }
        .header table { width: 100%; }
        .clinic-name { font-size: 24px; font-weight: bold; color: #1e3a8a; }
        .doc-name { font-size: 18px; font-weight: bold; color: #111827; }
        .doc-details { font-size: 10px; color: #4b5563; line-height: 1.4; }
        .patient-box { border: 1px solid #e5e7eb; border-radius: 5px; padding: 10px; margin-bottom: 20px; background-color: #f9fafb; }
        .patient-box table { width: 100%; font-size: 11px; }
        .vital-signs { width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 10px; text-align: center; }
        .vital-signs th { background-color: #eff6ff; border: 1px solid #bfdbfe; padding: 5px; font-weight: bold; color: #1e3a8a; }
        .vital-signs td { border: 1px solid #e5e7eb; padding: 5px; }
        .rx-symbol { font-size: 36px; font-weight: bold; color: #1e40af; margin-bottom: 10px; font-family: serif; }
        .medicine-item { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #e5e7eb; }
        .med-name { font-size: 14px; font-weight: bold; color: #111827; }
        .med-instructions { font-size: 12px; color: #374151; margin-top: 4px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 15px; }
        .signature-line { width: 250px; border-bottom: 1px solid #111827; margin: 0 auto 5px auto; }
        .signature-text { font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>

    @if(isset($isPatientCopy) && $isPatientCopy)
        <div style="position: absolute; top: 40%; left: 5%; width: 90%; transform: rotate(-35deg); text-align: center; z-index: -1; opacity: 0.15;">
            @if(isset($isExpired) && $isExpired)
                <div style="border: 8px solid #dc2626; padding: 20px; color: #dc2626;">
                    <h1 style="font-size: 60px; margin: 0; font-family: sans-serif; font-weight: 900; line-height: 1;">RECETA VENCIDA</h1>
                    <h2 style="font-size: 35px; margin: 0; font-family: sans-serif; font-weight: bold;">USO NO AUTORIZADO</h2>
                </div>
            @else
                <div style="border: 8px solid #1e3a8a; padding: 20px; color: #1e3a8a;">
                    <h1 style="font-size: 60px; margin: 0; font-family: sans-serif; font-weight: 900; line-height: 1;">COPIA DIGITAL</h1>
                    <h2 style="font-size: 35px; margin: 0; font-family: sans-serif; font-weight: bold;">DOCUMENTO DEL PACIENTE</h2>
                </div>
            @endif
        </div>
    @endif

    <div class="header">
        <table>
            <tr>
                <td width="50%" style="vertical-align: top;">
                    <div class="clinic-name">{{ $clinic->nombre }}</div>
                    <div class="doc-details">
                        Folio Receta: <strong>{{ $prescription->folio }}</strong><br>
                        Fecha: {{ \Carbon\Carbon::parse($prescription->emitted_at)->format('d/m/Y H:i') }}
                    </div>
                </td>
                <td width="50%" style="text-align: right; vertical-align: top;">
                    <div class="doc-name">{{ strtolower($doctorProfile->sexo ?? '') === 'femenino' ? 'Dra.' : 'Dr.' }} {{ $doctorUser->name }}</div>
                    <div class="doc-details">
                        Cédula Profesional: {{ $doctorProfile->cedula_profesional ?? 'En Trámite' }}<br>
                        Universidad: {{ $doctorProfile->universidad_egreso ?? 'No especificada' }}<br>
                        CLUES: {{ $clinic->clues ?? 'N/A' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="patient-box">
        <table>
            <tr>
                <td width="60%"><strong>Paciente:</strong> {{ $patient->nombre }}</td>
                <td width="20%"><strong>Edad:</strong> {{ \Carbon\Carbon::parse($patient->fecha_nacimiento)->age }} años</td>
                <td width="20%"><strong>Sexo:</strong> {{ $patient->sexo ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    @if($vitalSign)
    <table class="vital-signs">
        <tr>
            @if($vitalSign->peso) <th>Peso</th> @endif
            @if($vitalSign->talla) <th>Talla</th> @endif
            @if($vitalSign->temperatura) <th>Temp</th> @endif
            @if($vitalSign->presion_sistolica) <th>T.A.</th> @endif
            @if($vitalSign->frecuencia_cardiaca) <th>F.C.</th> @endif
            @if($vitalSign->oxigenacion) <th>SpO2</th> @endif
        </tr>
        <tr>
            @if($vitalSign->peso) <td>{{ $vitalSign->peso }} kg</td> @endif
            @if($vitalSign->talla) <td>{{ $vitalSign->talla }} m</td> @endif
            @if($vitalSign->temperatura) <td>{{ $vitalSign->temperatura }} °C</td> @endif
            @if($vitalSign->presion_sistolica) <td>{{ $vitalSign->presion_sistolica }}/{{ $vitalSign->presion_diastolica }}</td> @endif
            @if($vitalSign->frecuencia_cardiaca) <td>{{ $vitalSign->frecuencia_cardiaca }} lpm</td> @endif
            @if($vitalSign->oxigenacion) <td>{{ $vitalSign->oxigenacion }} %</td> @endif
        </tr>
    </table>
    @endif

    <div class="rx-symbol">Rx</div>

    <div>
        @foreach($prescription->items as $item)
            <div class="medicine-item">
                <div class="med-name">💊 {{ $item->medicamento }}</div>
                <div class="med-instructions">
                    <strong>Tomar:</strong> {{ $item->dosis }} | <strong>Frecuencia:</strong> {{ $item->frecuencia }} | <strong>Durante:</strong> {{ $item->duracion }}
                    @if($item->indicaciones_extra)
                        <br><span style="color: #4b5563; font-style: italic;">Nota: {{ $item->indicaciones_extra }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="footer">
        <div class="signature-line"></div>
        <div class="signature-text">Firma del Médico Autógrafo / Sello Digital</div>
        <div class="signature-text" style="margin-top: 5px;">Este documento es válido solo con firma autógrafa del médico tratante.</div>
    </div>

</body>
</html>