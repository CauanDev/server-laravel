<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório PDF</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            font-weight: 800;
            font-style: italic;
            background-color: #fff; /* Cor de fundo do corpo */
            display: flex;
            justify-content: center; /* Centraliza horizontalmente */
            align-items: center; /* Centraliza verticalmente */
            min-height: 100vh; /* Altura mínima da tela */
            flex-direction: column; /* Organiza os itens em coluna */
        }
        .container {
            max-width: 100%;
            text-align: center;
            margin-top: 10rem;
            margin-left: 2rem;
        }
        .text-4xl {
            font-size: 2.25rem;
        }
        .font-bold {
            font-weight: bold;
        }
        .table-auto {
            width: auto;
            margin-top: 1rem; /* Espaçamento superior da tabela */
        }
        .border {
            border: 1px solid black;
            padding: 0.5rem 1rem; /* Espaçamento interno das células */
        }
        img {
            width: 200px;
        }
        .abaixar{
            display: flex;
            flex-direction: column;
        }
        .teste{
            position: absolute;
            margin-left:14rem;            
            top: -4rem;


        }
        .horario {
            position: relative;
            font-size: 10px;
            top:-2rem;
        }
    </style>
</head>
<body>
    <div class="teste">
        <img src="https://img.freepik.com/vetores-premium/logotipo-mecanico_183875-451.jpg">
        <div class="horario abaixar">
            Emitido por: {{ $user }} {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
    <div class="container">
        <h1 class="text-4xl font-bold">Relatório {{ $title }}</h1>
        
        <table class="table-auto border-collapse">
            <thead>
                <tr>
                    @foreach ($headings as $heading)
                        <th class="border">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    @if (isset($item['contCars']))
                        <tr>
                            <td class="border">{{ $item['name'] }}</td>
                            <td class="border">{{ $item['age'] }}</td>
                            <td class="border">{{ $item['contServices'] }}</td>
                            <td class="border">{{ $item['contCars'] }}</td>
                            <td class="border">{{ $item['created_at'] }}</td>
                        </tr>
                    @elseif (isset($item['man']) || isset($item['woman']))
                        <tr>
                            <td class="border">{{ $item['man'] }}</td>
                            <td class="border">{{ $item['woman'] }}</td>
                        </tr>
                    @elseif (isset($item['owner_name']))
                        <tr>
                            <td class="border">{{ $item['owner_name'] }}</td>
                            <td class="border">{{ $item['name'] }}</td>
                            <td class="border">{{ $item['contServices'] }}</td>
                            <td class="border">{{ $item['last_service'] }}</td>
                        </tr>
                    @elseif (isset($item['brand']) && isset($item['total']))
                        <tr>
                            <td class="border">{{ $item['brand'] }}</td>
                            <td class="border">{{ $item['total'] }}</td>
                        </tr>
                    @elseif (isset($item['average']))
                        <tr>
                            <td class="border">{{ $item['name'] }}</td>
                            <td class="border">{{ $item['car'] }}</td>
                            <td class="border">{{ $item['average'] }}</td>
                            <td class="border">{{ $item['date'] }}</td>
                            <td class="border">{{ $item['price'] }}</td>
                        </tr>                        
                    @elseif (isset($item['brand']) && isset($item['gender']))
                        <tr>
                            <td class="border">{{ $item['gender'] }}</td>
                            <td class="border">{{ $item['brand'] }}</td>
                            <td class="border">{{ $item['total_cars'] }}</td>
                        </tr>
                    @elseif (isset($item['salary']))
                        <tr>
                            <td class="border">{{ $item['name'] }}</td>
                            <td class="border">{{ $item['salary'] }}</td>
                            <td class="border">{{ $item['contServices'] ?? '' }}</td>
                            <td class="border">{{ $item['created_at'] }}</td>
                        </tr>
                    @elseif (isset($item['email']))
                        <tr>
                            <td class="border">{{ $item['name'] }}</td>
                            <td class="border">{{ $item['email'] }}</td>
                            <td class="border">{{ $item['created_at'] }}</td>
                        </tr>
                    @elseif (isset($item['worker']))
                        <tr>
                            <td class="border">{{ $item['name'] }}</td>
                            <td class="border">{{ $item['price'] }}</td>
                            <td class="border">{{ $item['date'] }}</td>
                            <td class="border">{{ $item['worker'] }}</td>
                        </tr>                        
                    @endif
                @endforeach
            </tbody>
        </table>
        @if($subDataExist)
        <table class="table-auto border-collapse">
            <thead>
                <tr>
                    @foreach ($subHeadings as $heading)
                        <th class="border">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($subData as $item)
                    @if (isset($item['man']) || isset($item['woman']))
                        <tr>
                            <td class="border">{{ $item['man'] }}</td>
                            <td class="border">{{ $item['woman'] }}</td>
                        </tr>
                    @elseif (isset($item['brand']) && isset($item['total']))
                        <tr>
                            <td class="border">{{ $item['brand'] }}</td>
                            <td class="border">{{ $item['total'] }}</td>
                        </tr>
                    @elseif (isset($item['brand']) && isset($item['gender']))
                        <tr>
                            <td class="border">{{ $item['gender'] }}</td>
                            <td class="border">{{ $item['brand'] }}</td>
                            <td class="border">{{ $item['total_cars'] }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</body>
</html>
