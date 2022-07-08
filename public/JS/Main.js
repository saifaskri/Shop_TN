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

//Check for Multipale Img
var image_ups = document.getElementById("add_product_ProdIllustarion");
viewImageInScreen(image_ups,null);

//Check for Singel Img
var image_ups = document.getElementById("add_product_ProdImgView");
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
        if(Object.values(this.files).length <= 15 ){
            Object.values(this.files).forEach(file => {
                const myimg = file;
                // get file type and uppercase it
                var file_type =myimg.type.toUpperCase().split("/")[myimg.type.toUpperCase().split("/").length -1 ];
                show_file_type=myimg.name.split(".")[myimg.name.toUpperCase().split(".").length -1 ];
                // allowed file extention
                        // const allow_ext=["JPG","PNG","JPEG"];
                //define error array
                var errors = [];
                    //deal with type
                    if(! allow_ext.includes(file_type)){
                        errors.push("Wrong File Format Must be " +allow_ext+" Your File Format is "+show_file_type+" \n ");
                    }
    
                    // deal with size
                    if(myimg.size>max_photo_upload_size){
                        errors.push("Wrong File Size Must be under "+formatSizeUnits(max_photo_upload_size)+" Your File size is "+formatSizeUnits(myimg.size) +"\n");  
                    }
    
                    
                    if(img_screen && myimg && errors.length==0){
                        const reader = new FileReader();
                            reader.readAsDataURL(myimg);
                                reader.addEventListener("load",function(){
                                    img_screen.setAttribute("src",this.result)
                        })
    
                    }else if( !img_screen && errors.length==0){
                        return;
                    }else{
                        //here your costum error display
                        alert(errors);
                        image_up.value="";            
                    }
                    
                    
            });
        }else{
            //here your costum error display
            alert('Max Uploaded File Is 15');
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


//start Select options Looks Better
function addOptionToTheTopOfSelect(select,msg="Choose"){
 if(select){
    var opt = document.createElement('option');
     opt.value = "";
    opt.setAttribute('selected', true);
    opt.innerText = msg ;
    select.prepend(opt);
 }
}

function MakeCatAndSubCatMoreDynamic(SubCatogrieSelector,CategorieSelector){

    document.addEventListener('DOMContentLoaded', function() {

        

       if(CategorieSelector != undefined){

                if(CategorieSelector.value == 0  ||  CategorieSelector.value.length == 0  ){
                    SubCatogrieSelector.innerHTML = "";
                    addOptionToTheTopOfSelect(SubCatogrieSelector,"")
                }
            

                const SubCategorysDivsAll = document.querySelectorAll('.SubCategoryJsonFilter');
                var SubCategorysDatas = [];
                SubCategorysDivsAll.forEach(SubCategorysDiv => {
                    SubCategorysDatas.push(JSON.parse(SubCategorysDiv.dataset.subcategory));
                });

                CategorieSelector.onchange = function(){ 

                    if(CategorieSelector.value != 0 ){
                    SubCatogrieSelector.innerHTML = "";
                    addOptionToTheTopOfSelect(SubCatogrieSelector,"")
                    }   

                    SubCategorysDatas.forEach(SubCategory => {
                        if( SubCategory[2].MainCatId == this.value){
                            let opt = document.createElement('option');
                            opt.value = SubCategory[0].SubCatId;
                            opt.innerText = SubCategory[1].SubCatName ;
                            SubCatogrieSelector.appendChild(opt);
                        }  
                    });
                }
        }

    });


}

const Categorie = document.getElementById('add_product_category');
const SubCategorie = document.getElementById('add_product_SubCategory');
addOptionToTheTopOfSelect(Categorie);

MakeCatAndSubCatMoreDynamic(SubCategorie,Categorie);

const ModCategorie = document.getElementById('mod_product_category');
const ModSubCategorie = document.getElementById('mod_product_SubCategory');
MakeCatAndSubCatMoreDynamic(ModSubCategorie,ModCategorie);

const FilterCategorie = document.getElementById('filter_prod_back_ProdCat');
const FilterSubCategorie = document.getElementById('filter_prod_back_ProdSubCat');
MakeCatAndSubCatMoreDynamic(FilterSubCategorie,FilterCategorie);

// end make Select options Looks Better

