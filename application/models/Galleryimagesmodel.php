<?php
class GalleryImagesModel extends  CI_Model{
	var $table            = 'gallery_images';
	var $tableAs          = 'gallery_images a';
	var $tableViewAs      = 'view_gallery a';
	var $tabletags        = 'gallery_tags';
	var $tableParticipant = 'event_detail b';

    function __construct(){
       parent::__construct();
    }

	function records($where=array(),$isTotal=0){
		$alias['search_title'] = 'a.name';
		// $ttl_row = $this->db->get($this->tableAs)->num_rows();

	 	query_grid($alias,$isTotal);
		$this->db->select("a.*, b.name as category_name");
		$this->db->where('a.is_delete',0);
		$this->db->where('a.id_parent_lang is null');
		$this->db->join('gallery_category b','b.id = a.id_gallery_category');
		$query = $this->db->get($this->tableAs);

		if($isTotal==0){
			$data = $query->result_array();
		}
		else{
			return $query->num_rows();
		}

		$ttl_row = $this->records($where,1);
		
		// echo $this->db->last_query();
		return ddi_grid($data,$ttl_row);
	}

	function insert($data){
		$data['create_date'] 	= date('Y-m-d H:i:s');
		$data['user_id_create'] = id_user();
		$this->db->insert($this->table,array_filter($data));
		return $this->db->insert_id();
	}

	function insert_migrasi($data){
		// $data['create_date'] 	= date('Y-m-d H:i:s');
		// $data['user_id_create'] = id_user();
		$this->db->insert($this->table,array_filter($data));
		return $this->db->insert_id();
	}

	function selectData($id,$is_single_row=0){
		$this->db->select("a.*, b.name as category_name");
		$this->db->where('a.is_delete',0);
		$this->db->where('a.id',$id);
		$this->db->or_where('id_parent_lang',$id);
		$this->db->join('gallery_category b','b.id = a.id_gallery_category');
		if($is_single_row==1){
			return 	$this->db->get_where($this->tableAs)->row_array();
		}else{
			return 	$this->db->get_where($this->tableAs)->result_array();
		}
	}

	function update($data,$id){
		$where['id'] = $id;
		$data['user_id_modify'] = id_user();
		$data['modify_date'] 	= date('Y-m-d H:i:s');
		$this->db->update($this->table,$data,$where);
		return $id;
	}

	function updateKedua($data,$id){
		$where['id_parent_lang'] 	= $id;
		$data['user_id_modify'] 	= id_user();
		$data['modify_date'] 		= date('Y-m-d H:i:s');
		$this->db->update($this->table,$data,$where);
		return $id;
	}

	function updateByOther($data,$where){
		$data['user_id_modify'] = id_user();
		$data['modify_date'] 	= date('Y-m-d H:i:s');
		$this->db->update($this->table,$data,$where);
		return $id;
	}

	function delete($id){
		$delete = $this->db->delete($this->table, "id = ".$id);
		return $delete;
	}
	function delete2($id){
		$data = array(
            'is_delete' => 1,
            'user_id_modify' => id_user(),
            'modify_date' => date('Y-m-d H:i:s'),
        );
        $this->db->where('id_parent_lang', $id);
        $this->db->update($this->table, $data);
	}
	function findById($id){
		$where['a.id'] = $id;
		$where['is_delete'] = 0;
		return 	$this->db->get_where($this->table.' a',$where)->row_array();
	}
	function findBy($where,$is_single_row=0){
		$where['is_delete'] = 0;
		$this->db->select('*');
		if($is_single_row==1){
			return 	$this->db->get_where($this->tableAs,$where)->row_array();
		}
		else{
			return 	$this->db->get_where($this->tableAs,$where)->result_array();
		}
	}
	function listImages($where,$is_single_row=0){
		$where['is_delete'] = 0;
		$this->db->select('id as idImag, name as name_img, description as description_img, filename, id_lang, user_id_create, user_id_modify, create_date, modify_date, is_delete, rownum()-1 as rowNum');
		if($is_single_row==1){
			return 	$this->db->get_where($this->tableAs,$where)->row_array();
		}
		else{
			return 	$this->db->get_where($this->tableAs,$where)->result_array();
		}
	}

	function listImagesGallery($id_gallery = array() , $where,$id_tags=array()){
		$this->db->where('images_filename != "" ');
		$this->db->where_in('gallery_id', $id_gallery);
		$this->db->group_by('images_id');
		$this->db->order_by('images_create_date','desc');
		$images_by_gallery = $this->db->get_where($this->tableViewAs,$where)->result_array();		

		if ($id_tags) {

			$this->db->select('
								b.id as images_id,
								b.name as images_description,
								b.description as images_name,
								b.filename as images_filename
							');
			$this->db->where('a.id_images is not null or a.id_images !=""');
			$this->db->where_in('a.id_tags', $id_tags);
			$this->db->join('gallery_images b', 'b.id = a.id_images', 'left');
			$images_by_tags = $this->db->get($this->tabletags.' a')->result_array();
			$all_image = unique_multidim_array(array_merge($images_by_gallery, $images_by_tags),'images_id');
			$ret =  $all_image;

		}else{
			$ret =  $images_by_gallery;
		}
		return $ret;
	}

	function findCatBy($where,$is_single_row=0){
		$where['a.is_delete'] = 0;
		$this->db->select('b.name as category');
		$this->db->join('gallery_category b', 'b.id = a.id_gallery_category');
		if($is_single_row==1){
			return 	$this->db->get_where($this->tableAs,$where)->row_array();
		}
		else{
			return 	$this->db->get_where($this->tableAs,$where)->result_array();
		}
	}

	/*yang ditambah*/
	function findByNews($where,$is_single_row=0){
		$where['a.is_delete'] = 0;
		$this->db->select('a.*, b.name as category,b.uri_path as uri_path_category,a.is_experts,a.is_qa,a.teaser');
		$this->db->join('news_category b','b.id = a.id_news_category');
		if($is_single_row==1){
			return $this->db->get_where('news a',$where)->row_array();
		}
		else{
			return $this->db->get_where('news a',$where)->result_array();
		}
	}
	
	function fetchRow($where) {
		return $this->findBy($where,1);
	}
	
	function fetchRowCat($where) {
		return $this->findCatBy($where,1);
	}

	function records_participant($id,$where=array(),$isTotal=0){
		$alias['search_name'] = 'b.name';

	 	query_grid($alias,$isTotal);
		$this->db->select("b.*,a.id as gallery_id,a.id_parent_lang as parent_lang");
		$this->db->where('b.gallery_id',$id);
		$this->db->or_where('a.id_parent_lang',$id);
		$this->db->join($this->tableAs,'b.gallery_id = a.id');
		$query = $this->db->get($this->tableParticipant);


		if($isTotal==0){
			$data = $query->result_array();
		}
		else{
			return $query->num_rows();
		}

		$ttl_row = $this->records($where,1);
		
		// echo $this->db->last_query();
		return ddi_grid($data,$ttl_row);
	}
	function selectDataParticipant($id,$is_single_row=0){
		$this->db->where('id',$id);
		if($is_single_row==1){
			return 	$this->db->get_where($this->tableParticipant)->row_array();
		}else{
			return 	$this->db->get_where($this->tableParticipant)->result_array();
		}
	}

	function updateApprovaalParticipant($data,$id){
		$where['id'] = $id;
		$data['user_id_modify'] = id_user();
		$data['modify_date'] 	= date('Y-m-d H:i:s');
		$this->db->update($this->tableParticipant,$data,$where);
		return $id;
	}

	function getTotal($where=array(), $field='*') {
		$this->db->select($field);
		$this->db->where('is_delete',0);
		$this->db->order_by('id','desc');
		return 	$this->db->get_where($this->table,$where)->num_rows();
	}

	function getAll($where=array(), $perPage, $offset){
		$this->db->select('*');
		$this->db->where('is_delete',0);
		$this->db->order_by('id','desc');
		$this->db->limit($perPage, $offset);
		return 	$this->db->get_where($this->table,$where)->result_array();
	}
 }
