<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ALTERNATIVA CASANOVA - {{ $data->voucherType->name }} #{{ $data->id }}</title>

  <style>
    @page {
      margin: 1cm 1cm 2cm 1cm;

      @bottom-center {
        content: "Page " counter(page) " / " counter(pages);
        font-size: 0.75rem;
        color: #555;
      }
    }


    body {
      margin: 0;
      padding: 0;
      font-family: sans-serif;
      font-size: 0.85rem;
      line-height: 1.4;
      color: #111;
    }

    h1 {
      margin: 0.2rem 0;
      font-size: 1rem;
      font-weight: 700;
      line-height: 1.2;
    }

    .container {
      width: 100%;
      max-width: 1024px;
      margin-left: auto;
      margin-right: auto;
      padding-left: 0.5rem;
      padding-right: 0.5rem;
    }

    .grid-cols-3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.5rem;
    }

    .col-span-3 {
      grid-column: 1 / -1;
    }

    .border {
      border: 1px solid #e5e7eb;
    }

    .font-bold {
      font-weight: 700;
    }

    .inline-block {
      display: inline-block;
    }

    .text-center {
      text-align: center;
    }

    .text-end {
      text-align: right;
    }

    .m-1 {
      margin: 0.25rem;
    }

    .mt-3 {
      margin-top: 0.75rem;
    }

    .mb-3 {
      margin-bottom: 0.75rem;
    }

    .ms-1 {
      margin-left: 0.25rem;
    }

    .ms-3 {
      margin-left: 0.75rem;
    }

    .me-3 {
      margin-right: 0.75rem;
    }

    .w-32 {
      width: 8rem;
    }


    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 0.5rem;
      margin-bottom: 0.5rem;
      page-break-inside: avoid;
    }

    table thead {
      background-color: #f3f4f6;
    }

    table th,
    table td {
      border: 1px solid #e5e7eb;
      padding: 0.4rem 0.5rem;
      font-size: 0.85rem;
    }

    tr {
      page-break-inside: avoid;
    }

    .page-break {
      page-break-after: always;
    }
  </style>
</head>

<body>
  @php
  $chunks = $data->voucherDetails->chunk(20);
  $totalPages = $chunks->count();
  @endphp

  @foreach($chunks as $pageIndex => $detailsChunk)
  <div class="container">

    <div class="grid-cols-3">

      <div>
        <img src="{{ $logo }}" alt="Logo" style="width: 20%;">
      </div>


      <div class="ms-1 mt-3 mb-3">
        <h1 class="font-bold">ALTERNATIVA CASANOVA E.I.R.L</h1>
        <h1>{{ strtoupper($address->value) }}</h1>
      </div>


      <div class="border ms-3 me-3 text-center" style="padding: 0.5rem;">
        <h1 class="font-bold">
          {{ $data->voucherType->name === 'BOLETA' 
                ? 'BOLETA DE VENTA ELECTRÓNICA' 
                : 'FACTURA' }}
        </h1>
        <h1 class="font-bold">RUC: {{ $ruc->value }}</h1>
        <h1 class="font-bold">{{ $data->set . '-' . $data->correlative }}</h1>
      </div>
      <div class="border m-1 col-span-3" style="padding: 0.5rem;">
        <h1>
          <span class="inline-block w-32">Señor(es):</span>
          {{ $data->patient->name 
               . ' ' . $data->patient->paternal_surname 
               . ' ' . $data->patient->maternal_surname }}
        </h1>
        <h1>
          <span class="inline-block w-32">DNI:</span>
          {{ $data->patient->dni }}
        </h1>
        <h1>
          <span class="inline-block w-32">Tipo de Moneda:</span>
          SOLES
        </h1>
        <h1>
          <span class="inline-block w-32">Observación:</span>
          ---
        </h1>
      </div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Cantidad</th>
          <th>Presentación</th>
          <th>Descripción</th>
          <th>Precio (Inc. IGV)</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($detailsChunk as $vd)
        <tr>
          <td class="text-center">{{ $vd->amount }}</td>
          <td>
            @if($vd->medicine_id !== null)
            @php
            $pid = $vd->medicine->presentation;
            $presentation = $presentations->firstWhere('id', $pid);
            @endphp
            {{ $presentation 
                       ? $presentation->name 
                         . ' ' . $presentation->numeric_value 
                         . ' ' . $presentation->aux 
                       : 'UNIDAD' 
                  }}
            @else
            SERVICIO (1)
            @endif
          </td>
          <td>
            {{
                  $vd->medicine_id != null
                    ? $vd->medicine->name
                    : ($vd->treatment_id != null
                      ? $vd->treatment->name
                      : ($vd->appointment_id != null
                        ? 'CITA - DÍA: ' . $vd->appointment?->date 
                              . ' HORA: ' . $vd->appointment?->time 
                        : 'OTROS ~'))
                }}
          </td>
          <td class="text-end">{{ $vd->unit_price }}</td>
          <td class="text-end">{{ $vd->subtotal }}</td>
        </tr>
        @endforeach

        @if($pageIndex === $totalPages - 1)
        <tr>
          <td colspan="3"></td>
          <td>SUBTOTAL</td>
          <td class="text-end">{{ $data->subtotal }}</td>
        </tr>
        <tr>
          <td colspan="3"></td>
          <td>IGV</td>
          <td class="text-end">{{ $data->igv }}</td>
        </tr>
        <tr>
          <td colspan="3"></td>
          <td>TOTAL</td>
          <td class="text-end">{{ $data->total }}</td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>

  @if($pageIndex < $totalPages - 1)
    <div class="page-break">
    </div>
    @endif
    @endforeach
</body>

</html>