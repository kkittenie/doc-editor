@extends('layouts.app')

@section('content')

<div 
    class="p-5"
    x-data="documentEditor()"
>

    <!-- HEADER TOOLBAR -->
    <div class="flex gap-2 mb-4">
        <button 
            class="px-4 py-2 bg-blue-600 text-white rounded"
            @click="activeZone='header'"
        >
            Header
        </button>

        <button 
            class="px-4 py-2 bg-blue-600 text-white rounded"
            @click="activeZone='body'"
        >
            Body
        </button>

        <button 
            class="px-4 py-2 bg-blue-600 text-white rounded"
            @click="activeZone='footer'"
        >
            Footer
        </button>

        <button 
            class="px-4 py-2 bg-blue-600 text-white rounded"
            @click="activeZone='signature'"
        >
            Signature
        </button>

        <button
            class="ml-auto px-4 py-2 bg-green-600 text-white rounded"
            @click="saveDocument()"
        >
            Save
        </button>
    </div>


    <div class="grid grid-cols-2 gap-5">


        <!-- PANEL EDIT -->
        <div class="border rounded p-5">

            <h2 class="font-bold text-xl mb-4">
                Edit: <span x-text="activeZone"></span>
            </h2>


            <!-- HEADER -->
            <div x-show="activeZone === 'header'">

                <label>Kop Instansi</label>

                <input
                    class="border w-full p-2 mb-3"
                    x-model="kopInstansi"
                >

                <label>Alamat</label>

                <textarea
                    class="border w-full p-2"
                    x-model="kopAlamat"
                ></textarea>

            </div>



            <!-- BODY -->
            <div x-show="activeZone === 'body'">

                <label>Isi Dokumen</label>

                <textarea
                    rows="8"
                    class="border w-full p-2"
                    x-model="isiDokumen"
                ></textarea>

            </div>



            <!-- FOOTER -->
            <div x-show="activeZone === 'footer'">

                <label>Kota</label>

                <input
                    class="border w-full p-2 mb-3"
                    x-model="kota"
                >


                <label>Jabatan</label>

                <input
                    class="border w-full p-2"
                    x-model="jabatan"
                >

            </div>



            <!-- SIGNATURE -->
            <div x-show="activeZone === 'signature'">

                <label>Nama Penandatangan</label>

                <input
                    class="border w-full p-2"
                    x-model="nama"
                >

            </div>


        </div>




        <!-- PREVIEW -->
        <div class="border rounded p-5">

            <div class="flex justify-between mb-3">

                <button
                    class="border px-3 py-1"
                    @click="zoom--"
                >
                    -
                </button>


                <span x-text="zoom + '%'"></span>


                <button
                    class="border px-3 py-1"
                    @click="zoom++"
                >
                    +
                </button>

            </div>


            <div 
                class="border p-10 bg-white"
                :style="`transform:scale(${zoom/100}); transform-origin:top center`"
            >

                <h2 
                    class="text-center font-bold text-xl"
                    x-text="kopInstansi"
                ></h2>


                <p 
                    class="text-center"
                    x-text="kopAlamat"
                ></p>


                <hr class="my-5">


                <p x-text="isiDokumen"></p>



                <div class="mt-20 text-right">

                    <p x-text="kota"></p>

                    <br><br>

                    <b x-text="nama"></b>

                    <p x-text="jabatan"></p>

                </div>


            </div>

        </div>


    </div>

</div>



<script>

function documentEditor(){

    return {

        activeZone:'header',

        documentId:null,

        saveStatus:'idle',


        kopInstansi:'PT NUSANTARA CITRA MEDIA TBBK',

        kopAlamat:'Jl. Contoh Alamat',

        isiDokumen:'Isi perjanjian disini...',


        kota:'Cirebon',

        jabatan:'Direktur',

        nama:'Nama Penandatangan',


        zoom:100,


        saveDocument(){

            this.saveStatus='saving';


            console.log({
                header:this.kopInstansi,
                body:this.isiDokumen,
                footer:this.kota,
                signature:this.nama
            });


            setTimeout(()=>{

                this.saveStatus='saved';

                alert('Dokumen tersimpan');

            },500);

        }

    }

}

</script>


@endsection