// start write in input field and print it live in the card
// ************************************************************************************************************
//******write in a div and paste it live in an other div                                                      *
// *****all Selectore must be only >>>>>>>id                                                                  *
// ************************************************************************************************************
function write_and_paste(clavier,screen){
    if(clavier != undefined && screen != undefined){
        clavier.addEventListener("keyup",()=>{
            var text = clavier.value;
            screen.innerText= text;
                clavier.title=text;
        })
    }   
}
//end


var prod_name = document.getElementById("add_product_ProdName");
var card_prod_name = document.getElementById("card_name");
write_and_paste(prod_name,card_prod_name);

var desc = document.getElementById("add_product_ProdDescription");
var card_desc = document.getElementById("card-desc");
write_and_paste(desc,card_desc);



var price = document.getElementById("add_product_ProdPrice");
var card_price = document.getElementById("money");
write_and_paste(price,card_price);
// end write in input field and print it live in the card

//starting view photo befor upload
var image_ups = document.getElementById("add_product_ProdIllustarion");
var img_screens=document.getElementById("img_display_add");

viewImageInScreen(image_ups,img_screens);

// ************************************************************************************************************
// *****start upload from device and print it in screen befor upload to data base                             *
// *****accept only extention  that you choose but defualt is "JPG","GIF","PNG","JPEG";                       *
// *****max default size of uploaded file is 10Mb = 10485760 Octet you can edit it but value must be in Octet * 
// *****all Selectore must be only >>>>>>>id                                                                  *
// ************************************************************************************************************
function viewImageInScreen(image_up,img_screen,allow_exts=["JPG","PNG","JPEG"],max_photo_upload_size=10485760){
    allow_ext = allow_exts.map(name => name.toUpperCase());
    if (image_up != undefined){
    image_up.addEventListener("change",function(){
       const myimg =this.files[0];
       // get file type and uppercase it
       var file_type =myimg.type.toUpperCase().split("/")[myimg.type.toUpperCase().split("/").length -1 ];
       show_file_type=myimg.name.split(".")[myimg.name.toUpperCase().split(".").length -1 ];
       // allowed file extention
               // const allow_ext=["JPG","PNG","JPEG"];
       //define error array
       var errors = [];
       console.log( allow_ext.includes(file_type))

        //deal with type
        if(! allow_ext.includes(file_type)){
            errors.push("Wrong File Format Must be " +allow_ext+" Your File Format is "+show_file_type+" \n ");
        }

        // deal with size
        if(myimg.size>max_photo_upload_size){
            errors.push("Wrong File Size Must be under "+formatSizeUnits(max_photo_upload_size)+" Your File size is "+formatSizeUnits(myimg.size) +"\n");  
        }

        if(myimg && errors.length==0){
            const reader = new FileReader();
                reader.readAsDataURL(myimg);
                    reader.addEventListener("load",function(){
                        img_screen.setAttribute("src",this.result)
            })  
        }else{
            //here your costum error display
            alert(errors);
            image_up.value="";            
        }
       
   })
}
}

//end********************************************************************************************************************************************************************

//function to make bytes easier to read
function formatSizeUnits(bytes){
    if      (bytes >= 1073741824) { bytes = (bytes / 1073741824).toFixed(2) + " GB"; }
    else if (bytes >= 1048576)    { bytes = (bytes / 1048576).toFixed(2) + " MB"; }
    else if (bytes >= 1024)       { bytes = (bytes / 1024).toFixed(2) + " KB"; }
    else if (bytes > 1)           { bytes = bytes + " bytes"; }
    else if (bytes == 1)          { bytes = bytes + " byte"; }
    else                          { bytes = "0 bytes"; }
    return bytes;
  }